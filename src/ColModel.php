<?php

namespace PTK\Console\Table;

/**
 * Descreve o modelo de coluna
 *
 */
class ColModel {

    const ALIGN_LEFT = \STR_PAD_RIGHT;
    const ALIGN_RIGHT = \STR_PAD_LEFT;
    const ALIGN_CENTER = \STR_PAD_BOTH;

    protected string $name;
    protected string $label;
    protected int|float|null $width;
    protected int $align;

    /**
     * 
     * @param string $name Identificador único da coluna. É usado para relacionar o valor da célula com o colmodel adequado.
     * @param string $label Rótulo para a coluna. Usado no cabeçalho da tabela.
     * @param int|float|null $width Largura da coluna. Se ```int```, é o número de colunas do console; se ```float```, é um percentual da largura da tabela.
     * @param int $align Alinhamento do conteúdo da célula.
     */
    public function __construct(string $name, string $label = '', int|float|null $width = null, int $align = 1) {
        $this->name = $name;
        $this->label = $label;
        $this->width = $width;
        $this->align = $align;
    }

    public function setLabel(string $label): ColModel {
        $this->label = $label;
        return $this;
    }

    public function setWidth(int|float|null $width): ColModel {
        $this->width = $width;
        return $this;
    }

    public function setAlign(int $align): ColModel {
        $this->align = $align;
        return $this;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getLabel(): string {
        if ($this->label === '') {
            return $this->name;
        }
        return $this->label;
    }

    public function getWidth(): int|float|null {
        return $this->width;
    }

    public function getAlign(): int {
        return $this->align;
    }

}
