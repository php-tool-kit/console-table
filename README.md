# PHP Tool Kit/Console/Table

**Tabelas em texto e no console/terminal**

## Instalação

```composer require php-tool-kit/console-table```

## Uso

```php

/*
$data deve ser um array multidimensional com os dados em formato tabular linha->coluna->dados

Para um exemplo, consulte o arquivo devtest.php
*/

$tbl = new PTK\Console\Table\Table($data);

$tbl->setColModel(
    new PTK\Console\Table\ColModel('col_1', label: 'Coluna 1', width: 0.1, align: PTK\Console\Table\ColModel::ALIGN_CENTER),
    new PTK\Console\Table\ColModel('col_2', label: 'Coluna 2', width: 0.5, align: PTK\Console\Table\ColModel::ALIGN_LEFT),
    new PTK\Console\Table\ColModel('col_3', label: 'Coluna 3', width: 0.4, align: PTK\Console\Table\ColModel::ALIGN_RIGHT)
);

echo $tbl->output();

```

Para opções de configuração, consulte a documentação da API diretamente no código-fonte.

## Licença

**PHP Tool Kit/Console/Table** está licenciado sob a MIT Licence.