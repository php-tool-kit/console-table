<?php

namespace PTK\Console\Table;

/**
 * Exibe uma tabela com dados tabulares no console.
 *
 */
class Table {

    protected array $data = [];
    protected string $output = '';
    protected array $processed = [];
    protected array $colmodels = [];
    protected \League\CLImate\Util\System\System $system;
    protected array $colSpec = [];
    protected int $fullAvailableWidth = 0;
    protected string $verticalExternalBorderChar = '#';
    protected string $horizontalExternalBorderChar = '#';
    protected string $verticalInternalBorderChar = '|';
    protected string $horizontalInternalBorderChar = '-';
    protected string $horizontalHeaderBorderChar = '=';
    protected string $caption = '';

    public function __construct(array $data) {
        mb_internal_encoding('utf-8');
        $this->data = $data;
        $this->system = \League\CLImate\Util\System\SystemFactory::getInstance();
    }

    protected function prepare(): void {
        $this->processed = $this->splitCells($this->data);
        $this->processed = $this->equalizeLines($this->processed);

        $aligns = [];
        $widths = [];
        foreach ($this->colSpec as $name => $spec) {
            $aligns[$name] = $spec['align'];
            $widths[$name] = $spec['width'];
        }
        $this->processed = $this->alignCells($this->processed, $aligns, $widths);

//        print_r($this->processed);
//        exit();
    }

    protected function buildBody(): void {
//        print_r($this->processed);exit();
        $maxLines = $this->maxLines($this->processed);
        $names = $this->getColNames();

        $this->output .= $this->buildHorizontalHeaderBorder();

        foreach ($this->processed as $index => $row) {

            for ($i = 0; $i < $maxLines; $i++) {
                foreach ($names as $name) {
                    if ($name === $names[array_key_first($names)]) {
                        $this->output .= $this->verticalExternalBorderChar;
                    }
                    $this->output .= $row[$name][$i];
                    if ($name === $names[array_key_last($names)]) {
                        $this->output .= $this->verticalExternalBorderChar . PHP_EOL;
                    } else {
                        $this->output .= $this->verticalInternalBorderChar;
                    }
                }
            }

            if (array_key_last($this->processed) !== $index) {
                $this->output .= $this->buildHorizontalInternalBorder();
            }
        }

        $this->output .= $this->buildHorizontalExternalBorder();
    }

    protected function buildHorizontalExternalBorder(): string {
        return str_pad('', $this->fullAvailableWidth, $this->horizontalExternalBorderChar) . PHP_EOL;
    }

    protected function buildHorizontalHeaderBorder(): string {
        return $this->verticalExternalBorderChar . str_pad('', $this->fullAvailableWidth - 2, $this->horizontalHeaderBorderChar) . $this->verticalExternalBorderChar . PHP_EOL;
    }

    protected function buildHorizontalInternalBorder(): string {
        return $this->verticalExternalBorderChar . str_pad('', $this->fullAvailableWidth - 2, $this->horizontalInternalBorderChar) . $this->verticalExternalBorderChar . PHP_EOL;
    }

    protected function strPadUnicode($str, $pad_len, $pad_str = ' ', $dir = STR_PAD_RIGHT) {
        // cópia descara da de https://www.php.net/manual/en/function.str-pad.php#111147
        $str_len = mb_strlen($str);
        $pad_str_len = mb_strlen($pad_str);
        if (!$str_len && ($dir == STR_PAD_RIGHT || $dir == STR_PAD_LEFT)) {
            $str_len = 1; // @debug
        }
        if (!$pad_len || !$pad_str_len || $pad_len <= $str_len) {
            return $str;
        }

        $result = null;
        $repeat = ceil($str_len - $pad_str_len + $pad_len);
        if ($dir == STR_PAD_RIGHT) {
            $result = $str . str_repeat($pad_str, $repeat);
            $result = mb_substr($result, 0, $pad_len);
        } else if ($dir == STR_PAD_LEFT) {
            $result = str_repeat($pad_str, $repeat) . $str;
            $result = mb_substr($result, -$pad_len);
        } else if ($dir == STR_PAD_BOTH) {
            $length = ($pad_len - $str_len) / 2;
            $repeat = ceil($length / $pad_str_len);
            $result = mb_substr(str_repeat($pad_str, $repeat), 0, floor($length))
                    . $str
                    . mb_substr(str_repeat($pad_str, $repeat), 0, ceil($length));
        }

        return $result;
    }

    protected function alignCells(array $data, array $aligns, array $widths): array {
        $names = $this->getColNames();
        foreach ($data as $index => $row) {
            foreach ($row as $name => $cell) {
                $align = $aligns[$name];
                if ($name !== $names[array_key_last($names)]) {
                    $width = $widths[$name] - 2;
                } else {
                    $width = $widths[$name] - 1;
                }
                foreach ($cell as $key => $line) {
                    $data[$index][$name][$key] = $this->strPadUnicode($line, $width, ' ', $align);
                }
            }
        }

        return $data;
    }

    protected function equalizeLines(array $data): array {
        $maxLines = $this->maxLines($data);
        foreach ($data as $index => $row) {
            foreach ($row as $name => $cell) {
                if (sizeof($cell) < $maxLines) {
                    $count = $maxLines - sizeof($cell);
                    $data[$index][$name] = array_merge($cell, array_fill(array_key_last($cell) + 1, $count, ''));
                }
            }
        }

        return $data;
    }

    protected function maxLines(array $data): int {
        $maxLines = 0;

        foreach ($data as $row) {
            foreach ($row as $name => $cell) {
                $size = sizeof($cell);
                if ($maxLines < $size) {
                    $maxLines = $size;
                }
            }
        }

        return $maxLines;
    }

    protected function splitCells(array $data): array {
        $processed = [];
        foreach ($data as $index => $row) {
            foreach ($row as $name => $cell) {
                $processed[$index][$name] = mb_str_split($cell, $this->colSpec[$name]['width'] - 2);
            }
        }

        return $processed;
    }

    public function setColModel(ColModel ...$colmodels): Table {
        foreach ($colmodels as $model) {
            $this->colmodels[$model->getName()] = $model;
        }
        return $this;
    }

    /**
     * Prepara a especificação das colunas com base nos colmodels existentes.
     * @return void
     */
    protected function buildColSpec(): void {
        $colNames = $this->getColNames();

        foreach ($colNames as $name) {
            if (!key_exists($name, $this->colmodels)) {//se não tem um colmodel definido
                $this->setColModel(new ColModel($name));
            }
            $model = $this->colmodels[$name];
            $this->colSpec[$name] = [
                'label' => $model->getLabel(),
                'width' => $model->getWidth(),
                'align' => $model->getAlign()
            ];
        }

        $this->calculateWith();
    }

    /**
     * Calcula as larguras de colunas em colunas do console.
     * 
     * @return void
     */
    protected function calculateWith(): void {
        $widthUsed = 0; //colunas já usadas
        $avaliableWidth = $this->fullAvailableWidth; //saldo a utilizar
        //converte % em colunas
        foreach ($this->colSpec as $name => $spec) {
            if (is_float($spec['width'])) {
                $this->colSpec[$name]['width'] = (int) ($spec['width'] * $this->fullAvailableWidth);
            }
        }

        //calcula as colunas usadas parcialmente
        foreach ($this->colSpec as $name => $spec) {
            if (is_int($spec['width'])) {
                $widthUsed += $spec['width'];
                $avaliableWidth -= $spec['width'];
            }
        }

        //calcula as colunas com width = null
        $widthNull = 0; //quantidade de spec sem width
        foreach ($this->colSpec as $name => $spec) {
            if ($spec['width'] === null) {
                $widthNull++;
            }
        }
        $widthDefault = (int) ($avaliableWidth / $widthNull);
        if ($widthDefault < 1) {
            throw new \LogicException("The default width of columns cannot be less than 1 [$widthDefault].");
        }
        foreach ($this->colSpec as $name => $spec) {
            if ($spec['width'] === null) {
                $this->colSpec[$name]['width'] = $widthDefault;
                $widthUsed += $widthDefault;
                $avaliableWidth -= $widthDefault;
            }
        }

        //verifica se as colunas usadas não são maiores que as colunas totais
        if ($widthUsed > $this->fullAvailableWidth) {
            throw new \LogicException("The columns used [$widthUsed] are larger than the columns available in the console [{$this->fullAvailableWidth}].");
        }
    }

    /**
     * Detecta os nomes de colunas com base na primeira linha dos dados.
     * 
     * @return array
     */
    protected function getColNames(): array {
        return array_keys($this->data[array_key_first($this->data)]);
    }
    
    protected function buildHeader(): void {
        
        $names = $this->getColNames();

        $this->output .= $this->buildHorizontalHeaderBorder();

        foreach ($this->colSpec as $name => $spec){
            $label = $spec['label'];
            if($name === $names[array_key_last($names)]){
                $width = $spec['width']-1;
            }else{
                $width = $spec['width']-2;
            }
            
            
            if($name === $names[array_key_first($names)]){
                $this->output .= $this->verticalExternalBorderChar;
            }
            
            $this->output .= $this->strPadUnicode($label, $width, ' ', \STR_PAD_BOTH);
            if($name === $names[array_key_last($names)]){
                $this->output .= $this->verticalExternalBorderChar.PHP_EOL;
            }else{
                $this->output .= $this->verticalInternalBorderChar;
            }
        }
    }
    
    protected function buildCaption(): void {
        $this->output .= $this->buildHorizontalExternalBorder();
        
        $this->output .= $this->verticalExternalBorderChar.$this->strPadUnicode($this->caption, $this->fullAvailableWidth-2, ' ', \STR_PAD_BOTH).$this->verticalExternalBorderChar;
    }

    public function output(): string {
        //determina a largura total da tabela
        if ($this->fullAvailableWidth === 0) {
            $this->fullAvailableWidth = $this->system->width();
        }

        $this->buildColSpec();
        $this->prepare();
        
        if($this->caption !== ''){
            $this->buildCaption();
        }
        $this->buildHeader();
        $this->buildBody();

        return $this->output;
    }

    public function __toString(): string {
        return $this->output();
    }

    public function setHorizontalHeaderBorderChar(string $char): Table {
        $this->horizontalHeaderBorderChar = $char;
        return $this;
    }
    public function setHorizontalInternalBorderChar(string $char): Table {
        $this->horizontalInternalBorderChar = $char;
        return $this;
    }
    public function setVerticalInternalBorderChar(string $char): Table {
        $this->verticalInternalBorderChar = $char;
        return $this;
    }
    public function setHorizontalExternalBorderChar(string $char): Table {
        $this->horizontalExternalBorderChar = $char;
        return $this;
    }
    public function setVerticalExternalBorderChar(string $char): Table {
        $this->verticalExternalBorderChar = $char;
        return $this;
    }
    
    public function setTabelWidth(int $width): Table {
        $this->fullAvailableWidth = $width;
        return $this;
    }
    
    public function setCaption(string $caption): Table {
        $this->caption = $caption;
        return $this;
    }
}
