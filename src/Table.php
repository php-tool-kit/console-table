<?php

namespace PTK\Console\Table;

/**
 * Exibe uma tabela com dados tabulares no console.
 *
 */
class Table {

    protected array $data;
    protected array $colmodels = [];
    protected \League\CLImate\Util\System\System $system;
    protected array $colSpec = [];
    protected string $output = '';
    protected string $intersectionChar = '+';
    protected string $horizontalExternalBorderChar = '=';
    protected string $verticalExternalBorderChar = '#';
    protected string $horizontalInternalBorderChar = '-';
    protected string $verticalInternalBorderChar = '|';
    protected string $horizontalHeaderBorderChar = '=';

    /**
     * 
     * @var int Largura disponível total
     */
    protected int $fullAvailableWidth = 0;
    protected string $title = '';

    public function __construct(array $data) {
        $this->data = $data;
        $this->system = \League\CLImate\Util\System\SystemFactory::getInstance();
    }

    public function render(): string {
        //determina a largura total da tabela
        if ($this->fullAvailableWidth === 0) {
            $this->fullAvailableWidth = $this->system->width();
        }

        //prepara a especificação das colunas
        $this->buildColSpec();

        //quebra/alinhas as células
        $this->splitAndAlignCellContents();

        //monta a tabela em texto
        $this->buildOutput();

        return $this->output;
    }

    /**
     * Constroi a saída em texto.
     * 
     * @return void
     */
    protected function buildOutput(): void {
        $this->buildTitle();
        $this->buildHeader();
        
        foreach ($this->data as $index => $row) {

            $this->output .= $this->verticalExternalBorderChar;
            foreach ($row as $key => $lines) {
                foreach ($lines as $cell) {
                    $this->output .= $cell;
                    if ($key !== array_key_last($row)) {
                        $this->output .= $this->verticalInternalBorderChar;
                    }
                }
            }
            $this->output .= $this->verticalExternalBorderChar;
            $this->output .= PHP_EOL;

            if($index !== array_key_last($this->data)){
                $this->output .= $this->buildHorizontalInternalSeparator(); //linha de baixo
            }
        }
        
        $this->output .= $this->buildHorizontalExternalSeparator();
    }
    
    protected function buildTitle(): void {
        if($this->title === ''){
            return;
        }
        
        $this->output .= $this->verticalExternalBorderChar;
        $this->output .= str_pad('', $this->fullAvailableWidth - 4, $this->horizontalExternalBorderChar);
        $this->output .= $this->verticalExternalBorderChar;
        $this->output .= PHP_EOL;
        $this->output .= $this->verticalExternalBorderChar;
        $this->output .= str_pad($this->title, $this->fullAvailableWidth - 4, ' ', \STR_PAD_BOTH);
        $this->output .= $this->verticalExternalBorderChar;
        $this->output .= PHP_EOL;
    }
    
    protected function buildHeader(): void {
        $this->output .= $this->buildHorizontalExternalSeparator();
        
        $this->output .= $this->verticalExternalBorderChar;
        
        foreach($this->colSpec as $name => $spec){
            $label = $spec['label'];
            $width = $spec['width'] - 2;
            $align = $spec['align'];
            $this->output .= str_pad($label, $width, ' ', $align);
            if($name !== array_key_last($this->colSpec)){
                $this->output .= $this->verticalInternalBorderChar;
            }
        }
        
        $this->output .= $this->verticalExternalBorderChar;
        $this->output .= PHP_EOL;
        
        $this->output .= $this->buildHorizontalHeaderSeparator();
        
    }

    protected function buildHorizontalHeaderSeparator(): string {
        $output = $this->verticalExternalBorderChar;

        foreach ($this->colSpec as $key => $spec) {
            $output .= str_pad('', $spec['width'] - 2, $this->horizontalHeaderBorderChar);
            if ($key !== array_key_last($this->colSpec)) {
                $output .= $this->intersectionChar;
            }
        }

        $output .= $this->verticalExternalBorderChar;

        $output .= PHP_EOL;

        return $output;
    }
    
    protected function buildHorizontalExternalSeparator(): string {
        $output = $this->verticalExternalBorderChar;

        foreach ($this->colSpec as $key => $spec) {
            $output .= str_pad('', $spec['width'] - 2, $this->horizontalExternalBorderChar);
            if ($key !== array_key_last($this->colSpec)) {
//                $output .= $this->intersectionChar;
                $output .= $this->horizontalExternalBorderChar;
            }
        }

        $output .= $this->verticalExternalBorderChar;

        $output .= PHP_EOL;

        return $output;
    }
    
    protected function buildHorizontalInternalSeparator(): string {
        $output = $this->verticalExternalBorderChar;

        foreach ($this->colSpec as $key => $spec) {
            $output .= str_pad('', $spec['width'] - 2, $this->horizontalInternalBorderChar);
            if ($key !== array_key_last($this->colSpec)) {
                $output .= $this->intersectionChar;
            }
        }

        $output .= $this->verticalExternalBorderChar;

        $output .= PHP_EOL;

        return $output;
    }

//    protected function getTableWidth(): int {
//        $width = 0;
//        foreach ($this->colSpec as $spec){
//            $width += $spec['width'];
//        }
//        return $width;
//    }

    /**
     * Quebra o conteúdo das células de acordo com a largura total de cada coluna e alinha o conteúdo.
     * 
     * @return void
     */
    protected function splitAndAlignCellContents(): void {
        $cellHeight = 0; //altura máxima das células

        foreach ($this->data as $index => $row) {
            foreach ($row as $name => $cell) {
                $width = $this->colSpec[$name]['width'] - 2; //subtrai porque cada célula tem um separador em cada lado
                $align = $this->colSpec[$name]['align'];
                $content = str_split($cell, $width);
                foreach ($content as $key => $piece) {
                    $content[$key] = str_pad($piece, $width, ' ', $align);
                }
                if ($cellHeight < sizeof($content)) {
                    $cellHeight = sizeof($content);
                }

                $this->data[$index][$name] = $content;
            }
        }

        //coloca todas as linhas com a mesma altura
        foreach ($this->data as $index => $row) {
            foreach ($row as $name => $cell) {
                $width = $this->colSpec[$name]['width'] - 2; //subtrai porque cada célula tem um separador em cada lado
                if (sizeof($cell) < $cellHeight) {
                    $this->data[$index][$name] = array_fill(array_key_last($cell) + 1, $cellHeight, str_pad('', $width, ' '));
                }
            }
        }
    }

    /**
     * Define uma largura máxima para a tabela.
     * 
     * Se for definida uma largura maior que a largura do console, a tabela será gerada na largura definida.
     * 
     * @param int $cols
     * @return Table
     */
    public function setTableWidth(int $cols): Table {
        $this->fullAvailableWidth = $cols;
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
        if($widthDefault < 1){
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

    public function setColModel(ColModel ...$colmodels): Table {
        foreach ($colmodels as $model) {
            $this->colmodels[$model->getName()] = $model;
        }
        return $this;
    }

    public function __toString(): string {
        return $this->render();
    }

    public function setTitle(string $title): Table {
        $this->title = $title;
        return $this;
    }

    public function setIntersectionChar(string $char): Table {
        $this->intersectionChar = $char;
        return $this;
    }

    public function setVerticalInternalBorderChar(string $char): Table {
        $this->verticalInternalBorderChar = $char;
        return $this;
    }

    public function setHorizontalInternalBorderChar(string $char): Table {
        $this->horizontalInternalBorderChar = $char;
        return $this;
    }

    public function setHorizontalHeaderBorderChar(string $char): Table {
        $this->horizontalHeaderBorderChar = $char;
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

}
