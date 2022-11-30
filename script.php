<?php
require "vendor/autoload.php";

use Butschster\Dbml\DbmlParserFactory;

$parser = DbmlParserFactory::create();
$file = 'database.dbml';
$f = file_get_contents($file);
$f = str_replace('"', "", $f);

$schema = $parser->parse($f);
$tables = $schema->getTables();
$files = array();

foreach ($tables as $table) {
    $nameTable = strval($table->getName());
    $datePrefix = date('Y_m_d_His');
    $newfile = $datePrefix.'_create_'.$nameTable.'_table.php';
    $files[] = $newfile;


    if (!file_exists($newfile)) {
        $myfile = $newfile;
        
        $txt = '
    <?php
    
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;
    
    return new class extends Migration
    {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('.'"'.$nameTable.'"'.', function (Blueprint $table) {   

    ';
        file_put_contents($myfile, $txt, FILE_APPEND);  

        foreach($files as $file) {
            if (strpos($file, $nameTable) !== FALSE){
                //echo 'nameTab: '.$nameTable.' File'.$file;
                $table = $schema->getTable($nameTable);
                $columns = $table->getColumns();
                foreach($columns as $colu){
                    $nameColu = ($colu->getName());
                    $column = $schema->getTable($nameTable)->getColumn($nameColu);
                    $name = $column->getName();
                    $type = $column->getType()->getName();

                    if ($name !== 'id'){
                        if ($type == 'varchar' || $type == 'text'){
                            $lineContent = '               $table->string("'.$name.'");'. PHP_EOL;
                            
                        } else if ($type == 'data' || $type = 'datatime'){
                            $lineContent = '               $table->integer("'.$name.'");'. PHP_EOL;
    
                        } else if ($type == 'int' || $type = 'integer'){
                            $lineContent = '               $table->integer("'.$name.'");'. PHP_EOL;
    
                        } else if ($type = 'text'){
                            $lineContent = '               $table->text("'.$name.'");'. PHP_EOL;
    
                        }
                    } else {
                        $lineContent = '               $table->id();'. PHP_EOL;
                    }

                    

                    //Controlliamo se è pk
                    $pk = $column->isPrimaryKey();
                
                    if($pk == 1 && $name !== 'id'){
                        $lineContent = str_replace(';', "", $lineContent);
                        $lineContent .= '                   ->primary();'.PHP_EOL;
                        //echo 'DEFAULT: '.$lineContent;
                    }

                    //Controlliamo se esiste il default
                    $default = $column->getDefault(); 
                    
                    if($default !== null){
                        $value = $default->getValue();
                        $lineContent = str_replace(';', "", $lineContent);
                        $lineContent .= '                   ->default("'.$value.'");'.PHP_EOL;
                        //echo 'DEFAULT: '.$lineContent;
                    }

                   

                    //controlliamo se esiste [unique] ->unique();
                    $unique = $column->isUnique();
                    if($unique == 1){
                        $lineContent = str_replace(';', "", $lineContent);
                        $lineContent .= '                   ->unique();'.PHP_EOL;
                    }

                    //[nullable]
                    $nullable = $column->isNull();
        
                    if($nullable == 1 && $name !== 'id'){
                        $lineContent = str_replace(';', "", $lineContent);
                        $lineContent .= '                   ->nullable();'.PHP_EOL;
                    } else if ($name !== 'id'){
                        $lineContent = str_replace(';', "", $lineContent);
                        $lineContent .= '                   ->nullable(false);'.PHP_EOL;
                    }

                    $autoInc = $column->isIncrement();
                    if($autoInc == 1 && $name !== 'id'){
                        $lineContent = str_replace(';', "", $lineContent);
                        $lineContent = '           $table->increments('.$name.');'.PHP_EOL;
                    }
                   
                    file_put_contents($myfile, $lineContent, FILE_APPEND);
                }
                

            }
            
        }

        $finalText = '
               $table->timestamps();
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("'.$nameTable.'");
    }
    };
    ?>
        
        
        ';

        file_put_contents($myfile, $finalText, FILE_APPEND);  
    }
}
