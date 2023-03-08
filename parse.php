<?php


if(count($argv)> 2)
{
    echo "Incorrect number of arguments\n";
    exit(10);
}

if(count($argv) == 2)
{
    if($argv[1] == "--help")
    {
        echo "Skript typu filtr nacte ze standardniho vstupu zdrojovy kod v IPP-code20, zkontroluje lexikalni a syntaktickou spravnost kodu a vypise na standardni vystup XML reprezentaci programu.\n";
        exit(0); 
    }
    else
    {
        echo "Wrong argument\n";
        exit(10);
    }
}




$file = fopen("php://stdin", "r");
$cntr = 0;
$xw = xmlwriter_open_memory();
xmlwriter_set_indent($xw, 1);
$res = xmlwriter_set_indent_string($xw, ' ');

#generovanie hlavicky
xmlwriter_start_document($xw, '1.0', 'UTF-8');

xmlwriter_start_element($xw, 'program');
xmlwriter_start_attribute($xw, 'language');
xmlwriter_text($xw, 'IPPcode20');

#uprava riadkov pre detekovanie spravnosi hlavicky
do
{
    $line = fgets($file);
    $line = preg_replace('/#.*/', '', $line);
    $line = preg_replace("/[\r\n]+/", "\n", $line);
    $line = preg_replace('/\s+/', ' ',$line);
    $line = rtrim($line);
}while ($line == "");
if(!preg_match('/^\\s?\.ippcode20$/i', $line)) #kontrola spravnosti hlavicky
{
    echo "Syntax error";
    exit(21);
}

while(($line = fgets($file)))
{
    $isRead = false;
    $tmp = 0;
    $cntr++;
    if($line[0] == '#')
    {
        $cntr --;
        continue;
    }
    #uprava vsetkych riadkov pre jednoduchsie parsovanie, tj. odstranenie viacnasobnych medzier, enterov atd.
    $line = preg_replace('/#.*/', '', $line);
    $line = preg_replace("/[\r\n]+/", "\n", $line);
    $line = preg_replace('/\s+/', ' ',$line);
    $line = rtrim($line);
    #$line = "$line\n";
    $textArr[2] = 0;
    $textArr =  explode(" ",$line);
    $argNum = count($textArr)-2;
    $argNum2 = $argNum +1 ;
    
    $textArr[0] = strtoupper($textArr[0]);
    $count = count($textArr);

    #switch ktory kontroluje ci instrukcia existuje a ci ma spravne argumenty
    switch($textArr[0])
    {
        case "CREATEFRAME":
        case "PUSHFRAME":
        case "POPFRAME":
        case "RETURN":
        case "BREAK":
            if($argNum2 > 0)
            {
                echo "Syntax error";
                exit(23);
            }
            break;
        case "DEFVAR":
            if($argNum2 != 1)
            {
                echo "Syntax error";
                exit(23);
            }
            if (!preg_match('/(GF|TF|LF)@.*/', $textArr[1]))
            {
                exit(23);  
            }
            break;
        case "CALL":
            if($argNum2 != 1)
            {
                echo "Syntax error";
                exit(23);
            }
            if (preg_match('/(GF|TF|LF|int|string|bool|nil|)@.*/', $textArr[1]))
            {
                exit(23);  
            }
            break;
        case "PUSHS":
            if($argNum2 != 1)
            {
                echo "Syntax error";
                exit(23);
            }
            if (!preg_match('/(GF|TF|LF|string|nil|bool|int)@.*/', $textArr[1]))
            {
                exit(23);  
            }
            break;
        case "POPS":
            if($argNum2 != 1)
            {
                echo "Syntax error";
                exit(23);
            }
            if (!preg_match('/(GF|TF|LF)@.*/', $textArr[1]))
            {
                exit(23);  
            }
            break;
        case "WRITE":
            if($argNum2 != 1)
            {
                echo "Syntax error";
                exit(23);
            }
            if (!preg_match('/(GF|TF|LF|int|string|bool|nil|)@.*/', $textArr[1]) ) 
            {
                exit(23);
            }
            break;
        case "LABEL":
        case "JUMP":
            if($argNum2 != 1)
            {
                echo "Syntax error";
                exit(23);
            }
            if (preg_match('/(GF|TF|LF|int|string|bool|nil|)@.*/', $textArr[1]) ) 
            {
                exit(23);
            }
            break;
        case "EXIT":

            if($argNum2 != 1)
            {
                echo "Syntax error";
                exit(23);
            }
            if (!preg_match('/(GF|TF|LF|int|bool|string|nil)@.*/', $textArr[1]) ) 
            {
                exit(23);
            }
            break;
        case "DPRINT":
            if($argNum2 != 1)
            {
                echo "Syntax error";
                exit(23);
            }

            if (!preg_match('/(GF|TF|LF|int|string|bool|nil|)@.*/', $textArr[1]) ) 
            {
                exit(23);
            }
            break;
        case "MOVE":
            if($argNum2 != 2)
            {
                echo "Syntax error";
                exit(23);
            }
            if (!preg_match('/(GF|TF|LF)@.*/', $textArr[1]) || !preg_match('/(string|int|bool|nil|GF|TF|LF)@.*/', $textArr[2]) ) 
            {
                exit(23);
            }
            break;
        case "INT2CHAR":
            if($argNum2 != 2)
            {
                echo "Syntax error";
                exit(23);
            }
            if (!preg_match('/(GF|TF|LF)@.*/', $textArr[1]) || !preg_match('/(int|GF|TF|LF|nil|string|bool)@.*/', $textArr[2]) ) 
            {
                exit(23);
            }
            break;
        case "READ":
            $isRead = true;
            if($argNum2 != 2)
            {
                echo "Syntax error";
                exit(23);
            }
            if (!preg_match('/(GF|TF|LF)@.*/', $textArr[1]) || !preg_match('/^(int|string|bool)$/', $textArr[2])) 
            {
                exit(23);
            }
            break;
        case "STRLEN":
            if($argNum2 != 2)
            {
                echo "Syntax error";
                exit(23);
            }
            if (!preg_match('/(GF|TF|LF)@.*/', $textArr[1]) || !preg_match('/(int|GF|TF|LF|nil|string|bool)@/', $textArr[2]) ) 
            {
                exit(23);
            }
            break;
        case "TYPE":
            if($argNum2 != 2)
            {
                echo "Syntax error";
                exit(23);
            }

            if (!preg_match('/(GF|TF|LF)@.*/', $textArr[1]) || !preg_match('/(int|string|bool|GF|LF|TF|nil)@/', $textArr[2]) ) 
            {
                exit(23);
            }
            break;
        case "ADD":
        case "SUB":
        case "MUL":
            
        case "IDIV":
            if($argNum2 != 3)
            {
                echo "Syntax error";
                exit(23);
            }
            if (!preg_match('/(GF|TF|LF)@.*/', $textArr[1]) || !preg_match('/(int|bool|string|nil|GF|TF|LF)@.*/', $textArr[2]) || !preg_match('/(int|bool|string|nil|GF|TF|LF)@.*/', $textArr[3]))
            {
                exit(23);  
            }
            break;
        case "LT":
        case "GT":
        case "EQ":
        case "AND":
        case "OR":
            if($argNum2 != 3)
            {
                echo "Syntax error";
                exit(23);
            }
            if (!preg_match('/(GF|TF|LF)@.*/', $textArr[1]) || !preg_match('/(bool|string|int|nil|GF|TF|LF)@.*/', $textArr[2]) || !preg_match('/(bool|string|int|nil|GF|TF|LF)@.*/', $textArr[3]))
            {
                exit(23);  
            }
            break;
        case "NOT":
            if($argNum2 != 2)
            {
                echo "Syntax error";
                exit(23);
            }
            if (!preg_match('/(GF|TF|LF)@.*/', $textArr[1]) || !preg_match('/(bool|string|int|nil|GF|TF|LF)@.*/', $textArr[2]))
            {
                exit(23);  
            }
            break;
        case "STRI2INT":
            if($argNum2 != 3)
            {
                echo "Syntax error";
                exit(23);
            }
            if (!preg_match('/(GF|TF|LF)@.*/', $textArr[1]) || !preg_match('/(int|GF|TF|LF|nil|string|bool)@.*/', $textArr[2]) || !preg_match('/(int|GF|TF|LF|nil|string|bool)@.*/', $textArr[3]))
            {
                exit(23);  
            }
            break;
        case "CONCAT":
            if($argNum2 != 3)
            {
                echo "Syntax error";
                exit(23);
            }
            if (!preg_match('/(GF|TF|LF)@.*/', $textArr[1]) || !preg_match('/(string|bool|int|nil|GF|TF|LF)@.*/', $textArr[2]) || !preg_match('/(string|bool|int|nil|GF|TF|LF)@.*/', $textArr[3]))
            {
                exit(23);  
            }
            break;
        case "GETCHAR":
            if($argNum2 != 3)
            {
                echo "Syntax error";
                exit(23);
            }
            if (!preg_match('/(GF|TF|LF)@.*/', $textArr[1]) || !preg_match('/(int|GF|TF|LF|nil|string|bool)@.*/', $textArr[2]) || !preg_match('/(int|GF|TF|LF|nil|string|bool)@.*/', $textArr[3]))
            {
                exit(23);  
            }
            break;
        case "SETCHAR":
            if($argNum2 != 3)
            {
                echo "Syntax error";
                exit(23);
            }
            if (!preg_match('/(GF|TF|LF)@.*/', $textArr[1]) || !preg_match('/(int|GF|TF|LF|nil|string|bool)@.*/', $textArr[2]) || !preg_match('/(int|GF|TF|LF|nil|string|bool)@.*/', $textArr[3]))
            {
                exit(23);  
            }
            break;
        case "JUMPIFEQ":
        case "JUMPIFNEQ":
            if($argNum2 != 3)
            {
                echo "Syntax error";
                exit(23);
            }

            if (preg_match('/(int|string|bool|nil|GF|TF|LF)@.*/', $textArr[1]) || !preg_match('/(int|string|bool|nil|GF|TF|LF)@.*/', $textArr[2]) || !preg_match('/(string|int|bool|nil|GF|TF|LF)@.*/', $textArr[3]))
            {
                exit(23);  
            }
            break;
        case "":
            xmlwriter_end_element($xw);
            echo xmlwriter_output_memory($xw);
            exit(0);
        default:
            echo "Syntax error";
            exit(22);  
    }

    #zaciatok generovania vysledneho XML
    xmlwriter_start_element($xw, "instruction");
    xmlwriter_start_attribute($xw, "order");
    xmlwriter_text($xw, $cntr);
    xmlwriter_start_attribute($xw,"opcode");
    
    xmlwriter_text($xw, "$textArr[0]");
    xmlwriter_end_attribute($xw);
    for($i = 1; $i <= $argNum+1; $i++)
    {
        
        xmlwriter_start_element($xw, "arg$i");
        xmlwriter_start_attribute($xw, "type");
        if(preg_match("/.*@{2,}.*/", $textArr[$i])) #kontrola pre pripad dvojicu @. napr int@@42
        {
            exit(23);
        }
        $type = explode('@', $textArr[$i], 2);
        if(count($type) < 2)
        {
            $type[1] = "";
        }
        

        if(($type[0] == "bool" && ($type[1] != "true" && $type[1] != "false")) && $isRead == false) #kontrola aby za bool@ mohlo byt aba true alebo false
        {
            exit(23);
        }
        if($type[0] == "GF" || $type[0] == "LF" || $type[0] == "TF" ) 
        {
            if(preg_match('/(^[0-9]|[^_\\-\\$&%\\*!\\?\\w])/', $type[1])) #kontrola poziadaviek na nazov premennej <var>
            {
                echo "Syntax error\n";
                exit(23);
            }
            $type[1] = "$type[0]@$type[1]";
            $type[0] = "var";
        }
        elseif($type[0] != "int" && $type[0] != "string" && $type[0] != "bool" && $type[0] != "nil")
        {
            $type[1] = $type[0]; 
            $type[0] = "label";
            if(preg_match('/(^[0-9]|[^_\\-\\$&%\\*!\\?\\w])/', $type[1])) #kontrola spravnosti nazvu label
            {
                echo "Syntax error\n";
                exit(23);
            }
            
        }
        if($type[0] == "string")
        {
            if(preg_match('/\\\/', $type[1]))
            {
                if(preg_match('/((\\\[0-9]{0,2}[a-zA-Z])|\\\s)/', $type[1]) || preg_match('/\\\$/', $type[1]) ) #kontrola escape sekvencii '/'
                {
                    exit(23);
                }
            }
        }

        if(($type[0] == "int" || $type[0] == "bool" || $type[0] == "nil" || $type[0] == "GF" || $type[0] == "TF" || $type[0] == "LF") && $isRead == false)
        {
           if($type[1] == "") #kontrola prazdneho nazvu premmenej
           {
               exit(23);
           } 

           if($type[0] == "nil" && $type[1] != "nil")
           {
               exit(23);
           }
        }
        $temp = $type[0];
        if($isRead == true && $i%2 == 0) #specialny pripad pre instrkuciu READ kedze typ jej druheho argumentu je <type>
        {
            $temp = $type[0];
            $type[0] = "type";
            $type[1] = $temp;
        }
        xmlwriter_text($xw, "$type[0]");
        xmlwriter_end_attribute($xw);
        xmlwriter_text($xw, "$type[1]");
        xmlwriter_end_element($xw);
    }
    xmlwriter_end_element($xw);  
    $isRead = false;
}
#ukoncenie xml kodu a print na stdout
xmlwriter_end_element($xw);
echo xmlwriter_output_memory($xw);
exit (0);
