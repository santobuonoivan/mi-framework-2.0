<?php

namespace Mixplay;


class BattleShipPlayer
{
    const FILAS = 10;
    const COLUMNAS = 10;
    private $table = [];
    private $enemyTable = [];
    private $ships = [];
    private $name = '';
    private $lose;
    private $iA;
    private $lastIA;
    private $shots;
    private $fails;
    private $success;
    

    public function __construct( string $name, $player = null)
    {   
        if($player === null){
            $this->name = $name;
            $this->createTables(10);
            $this->lose = $this->createShips(4);
            $this->setShips();
            $this->shots = 0;
            $this->fails = 0;
            $this->success = 0;
    
            //IA
            $this->iA=0;
            $this->lastIA=0;
        }else{
            $this->table = $player->table;
            $this->enemyTable = $player->enemyTable;
            $this->ships = $player->ships;
            $this->name = $player->name;
            $this->lose =$player->lose;
            $this->iA = $player->iA;
            $this->lastIA = $player->lastIA;
            $this->shots = $player->shots;
            $this->fails = $player->fails;
            $this->success = $player->success;
        }
    }
    public function setIA($coord)
    {
        $values = ($coord);
        $this->iA=$values;
        if ($this->lastIA==0)
        {
            $this->lastIA=$values;
        }
 
    }
    public function receiveShot($coords)
    {
        $llegaAReceiveShot = "{$this->getName()} receiveShot BattleShipPlayer in : ". json_encode($coords) ."\n";


        $values = $coords;
        $x = $values[0];
        $y = $values[1];
        $response = '';
        if($this->table[$x][$y]==='A')
        {
            //echo "Agua en ($x,$y)</br>";
            $response = 'AGUA';
        }elseif( $this->table[$x][$y]!='X' ) {
            //echo "Barco en ($x,$y)</br>";
            $response='BARCO';
            $this->lose--;
        }else
        {
            $response = "revisar xq llega a una coordenada X en ($x,$y)</br>";
            return $response;
        }
        $this->table[$x][$y]='X';
        $a = new \stdClass;
        $a->respuestaShot = $response;
        $a->llegaAReceiveShot = $llegaAReceiveShot;
        return $a;

    }
    public function sendShot()
    {
        $posible = false;
        while (!$posible) {
            if($this->iA == 0 && $this->lastIA == 0)
            {
                $a = rand(0,9);
                $b = rand(0,9);
                $posible = $this->available($a,$b,'E');
            }elseif($this->iA != 0 )
                {

                    // (+1, )
                    $a = $this->iA[0]+1;
                    $b = $this->iA[1];
                    $posible = $this->available($a,$b,'E');
                    if($posible)
                    {
                        break;
                    }

                    // (-1, )

                    $a = $this->iA[0]-1;
                    $b = $this->iA[1];
                    $posible = $this->available($a,$b,'E');
                    if($posible)
                    {
                        break;
                    }

                    // ( ,+1)

                    $a = $this->iA[0];
                    $b = $this->iA[1]+1;
                    $posible = $this->available($a,$b,'E');
                    if($posible)
                    {
                        break;
                    }

                    // ( ,-1)

                    $a = $this->iA[0];
                    $b = $this->iA[1]-1;
                    $posible = $this->available($a,$b,'E');
                    if($posible)
                    {
                        break;
                    }

                    // ya no sirve la coordenada de referencia
                    $this->iA=0;
                }else{
                    // (+1, )
                    $a = $this->lastIA[0]+1;
                    $b = $this->lastIA[1];
                    $posible = $this->available($a,$b,'E');
                    if($posible)
                    {
                        break;
                    }

                    // (-1, )

                    $a = $this->lastIA[0]-1;
                    $b = $this->lastIA[1];
                    $posible = $this->available($a,$b,'E');
                    if($posible)
                    {
                        break;
                    }

                    // ( ,+1)

                    $a = $this->lastIA[0];
                    $b = $this->lastIA[1]+1;
                    $posible = $this->available($a,$b,'E');
                    if($posible)
                    {
                        break;
                    }

                    // ( ,-1)

                    $a = $this->lastIA[0];
                    $b = $this->lastIA[1]-1;
                    $posible = $this->available($a,$b,'E');
                    if($posible)
                    {
                        break;
                    }
                    
                    // ya no me sirve el punto de referencia
                    $this->lastIA = 0;

            }
        }
        //echo "posible send a ($a,$b)";
            $this->enemyTable[$a][$b] = 'X';
            $coords[]=$a;
            $coords[]=$b;
            $this->shots++;
            //echo "sending: {$this->getName()}". json_encode($coords)."</br>";
        return $coords;
    }
    public function createTables(int $n) 
    {
        for ($i=0; $i < $n; $i++) 
        {             
            for ($y=0; $y < $n; $y++) 
            { 
                $this->table[$i][$y] = 'A';
                $this->enemyTable[$i][$y] = 'A';              
            }
        }
    }
    public function setShips()
    {
        for ($vuelta=0; $vuelta < count($this->ships); $vuelta++) {        
            $P = rand(0,9);
            $T = rand(0,9);
            
            if( $this->available($P,$T) )
            {   
                $direction = $this->direction($P,$T, count($this->ships[$vuelta]) );
                
                switch ($direction) {
                    case '':
                        //echo 'no-exist-space '. "</br>";
                        $vuelta--; //está disponible pero no entra el barco para ninguna dirección
                        break;

                    case 'H+':
                        for ($i=0; $i < count($this->ships[$vuelta]) ; $i++) { // HORIZONTALMENTE derecha
                            $this->table[$P][$T+$i] = $this->ships[$vuelta][$i];
                        }
                        break;

                    case 'H-':
                        for ($i=0; $i < count($this->ships[$vuelta]) ; $i++) { // HORIZONTALMENTE izquierda
                            $this->table[$P][$T-$i] = $this->ships[$vuelta][$i];
                        }
                        break;

                    case 'V+':
                        for ($i=0; $i < count($this->ships[$vuelta]) ; $i++) { // VERTICALMENTE ARRIBA
                            $this->table[$P+$i][$T] = $this->ships[$vuelta][$i];
                        }
                        break;

                    case 'V-':
                        for ($i=0; $i < count($this->ships[$vuelta]) ; $i++) { // VERTICALMENTE ABAJO
                            $this->table[$P-$i][$T] = $this->ships[$vuelta][$i];
                        }
                        break;

                    default:
                        echo ":( revisar switch";
                        break;
                }
                
            }else{
                //echo "coordenada no disponible en ($P,$T) valor: {$this->table[$P][$T]} </br>";
                $vuelta--; // no disponible la coordenada
            }                      
        }
    }
    public function printTable()
    {   
        $loader = new \Twig_Loader_Filesystem( './templates');
        $twig = new \Twig_Environment( $loader, [] );
        $tablero = $this->table;
        $enemyTable = $this->enemyTable;
        $name = $this->name;
        
        return ( $twig->render( 'tablero.twig', compact( 'tablero' , 'enemyTable', 'name') ) );
    }
    public function printShips()
    {   
        echo "[ ";
        for ($i=0; $i < count($this->ships)-1; $i++) { 
            echo count($this->ships[$i]).",";
        }
        echo count( $this->ships[count($this->ships)-1]) ." ]</br>";        
        
    }
    public function available(int $coordX, int $coordy, $enemy = null)
    {
        if( ($coordX<10 && $coordX>=0) && ($coordy<10 && $coordy>=0))
        {
            if ($enemy != null)
            {
                $value = $this->enemyTable[$coordX][$coordy];
            }else{
                $value = $this->table[$coordX][$coordy];
            }            
            //echo $value . "($coordX , $coordy)</br>";
            return 'A'=== $value;
        }else {
            //echo "fuera del tablero  ($coordX,$coordy) </br>";
            return false;
        }
    }
    public function direction(int $x, int $y, int $count):string
    {
        $case = rand(0,3);

        switch ($case) {
            case 0:
                $result = true;
                for ($i=0; $i < $count && $result ; $i++) { // valida si entra el barco  HORIZONTALMENTE derecha
                    
                    if(!$this->available( $x , $y+$i ))
                    {
                        $result = false;
                    }
                }
                if($result == true){
                    return 'H+';
                }
                break;
            
            case 1:
                $result = true;
                for ($i=0; $i < $count && $result ; $i++) { // valida si entra el barco  HORIZONTALMENTE izquierda
                    if(!$this->available( $x , $y-$i ))
                    {
                        $result = false;
                    }
                }
                if($result == true){
                    return 'H-';
                }
            break;
            case 2:
                $result = true;
                for ($i=0; $i < $count && $result ; $i++) { // valida si entra el barco  VERTICALMENTE arriba
                    if(!$this->available($x+$i, $y))
                    {
                        $result = false;
                    }
                }
                if($result == true){
                    return 'V+';
                }
            break;
            case 3:
                $result = true;
                for ($i=0; $i < $count && $result ; $i++) { // valida si entra el barco  VERTICALMENTE arriba
                    if(!$this->available($x+$i, $y))
                    {
                        $result = false;
                    }
                }
                if($result == true){
                    return 'V+';
                }else {
                    return '';
                }
            break;     
                
            default:
            echo "llega al defoult del switch del direction con case: $case";
            return '';
                break;
            
        }
        return '';
    }
    public function createShips(int $k)
    {
        $types = $k;
        $h=0;
        for ($i=1; $i <= $types; $i++) { //tipos de barcos 
            for ($n=0 ; $n < $types-($i-1) ; $n++) { //cantidad de cada tipo
                for ($t=0; $t < $i; $t++) { //largo dinamico de un tipo
                    $ship[] = $i;
                    $h++;
                }
                $this->ships[]=$ship;
                $ship = [];
            }
        }
        //echo " ship parts: $h";
        return $h;
    }
    public function lose()
    {
        //echo $this->lose."</br>";
        return $this->lose;    
    }
    public function getName()
    {
        return $this->name;
    }
    public function serializeToJson() : string {
        $obj = new \stdClass;
        $obj->table = $this->table;
        $obj->enemyTable = $this->enemyTable;
        $obj->ships = $this->ships;
        $obj->name = $this->name;
        $obj->lose = $this->lose;
        $obj->iA = $this->iA;
        $obj->lastIA = $this->lastIA;        
        $obj->shots = $this->shots;
        $obj->fails = $this->fails;
        $obj->success =$this->success;
        return serialize( $obj );
    }
    public function getTable(){
        return $this->table;
    }
    public function setEnemyTable($table){
        $this->enemyTable = $table;
    }
}
