<?php

/**
 * Scissor (http://craftsware.net/scissor)
 *
 * @link      https://github.com/craftsware/scissor
 * @license   https://github.com/craftsware/scissor/LICENSE.md (MIT License)
 * @copyright Copyright (c) 2017 Ruel Mindo
 *
 */

namespace Craftsware\Lib;


/**
 * Database
 */
class Database {


    /**
    * Database Settings
    *
    * @param array $database
    */
    private $settings = [];



    /**
    * Constructor
    *
    * @param array $args
    */
    public function __construct($settings)
    {
        if(is_array($settings)) {

            $this->settings = $settings;
        }
    }



    /**
    * Get Maximum ID of a table
    *
    * @param $table String
    * @param $add Int
    */
    public function max($table, $inc = null)
    {
        return $this->select($table, ['MAX(id)'])[0]['MAX(id)'] + (isset($inc) ? $inc : 0);
    }



    /**
    * Query
    *
    * @param $string String
    * @param Object
    */
    public function query($string)
    {
        return $this->connection()->query($string);
    }



    /**
    * Get Data from the table
    *
    * @param $query String
    */
    public function fetch($query)
    {
        $db = $this->connection();

        if(method_exists($result = $db->query(rtrim(preg_replace('/\s\s/', ' ', $query), ' ')), 'rowCount')) {

            if($result->rowCount() && $result->setFetchMode($db::FETCH_ASSOC)) {

                return $result->fetchAll();
            }

            return false;
        }
    }




    private function limit($args)
    {
        if(isset($args['LIMIT']) && is_int($args['LIMIT']) ) {

            return "LIMIT ". $args['LIMIT'];
        }
    }



    public function delete($table, $args)
    {
        return $this->query("DELETE FROM ". $table ." ". implode(' ', $this->syntax($args)));
    }



    public function insert($table, $args)
    {
        return $this->query("INSERT INTO ". $table ." (`". implode("`,`", array_keys($args)) . "`) VALUES ('". implode("','", $args) . "')");
    }



    public function select($table, $column = ['*'], $args = null)
    {
        // Default
        $syntax[] = "SELECT ". implode(',', $column) ." FROM $table";

        if(isset($args)) {

            if(count($syntax = array_merge($syntax, $this->syntax($args))) > 1 ) {

                return $this->fetch(implode(' ', $syntax));
            }
        
        } else {

            return $this->fetch($syntax[0]);
        }
    }



    public function update($table, $args)
    {
        $SET = '';

        if(isset($args['SET']) && !empty($args['SET'])) {

            foreach($args['SET'] as $key => $value) {

                $SET .= $key ."='". $value . "', ";
            }

            $SET = rtrim($SET, ", ");
        }

        if($this->query("UPDATE ". $table . " SET ". $SET ." ". implode(' ', $this->syntax($args)))) {

            return $this->select($table, ['*'], ['WHERE' => $args['WHERE']])[0];
        }
    }



    /**
    * Migrate
    *
    * @param array $tables
    * @return JSON $migrate
    *
    */
    public function migrate($dir)
    {
        $migrate = [];


        if(file_exists($directory = '../App/'. $dir)) {

            foreach(array_reverse(glob($directory .'/*.php')) as $path) {

                $namespace = str_replace('/', '\\', 'App'. explode('.php', explode('App', $path)[1])[0]);

                if(method_exists($object = (new $namespace), 'create')) {

                    if($table = $object->create($this)) {

                        $migrate['created'][] = basename($namespace);
                    
                    } else {

                        $migrate['failed'][] = basename($namespace);
                    }

                } else {

                    $migrate['error'] = 'Method '. basename($namespace) . ':create() not exist.';
                }
            }

        } else {

            $migrate['error'] = 'Directory ' . $dir .' not exist.';
        }

        return $migrate;
    }



    private function syntax($args)
    {
        $syntax = [];


        if($condition = $this->condition($args)) {

            $syntax[] = $condition;
        }

        if($orderby = $this->orderby($args)) {

            $syntax[] = $orderby;
        }

        if($limit = $this->limit($args)) {

            $syntax[] = $limit;
        }

        return $syntax;
    }




    private function orderby($args)
    {
        $order = '';
        $column = '';

        if(isset($args['ORDERBY']) ) {

            extract($args);

            if(is_array($ORDERBY['column'])) {

                $column = implode(', ', $ORDERBY['column']);
            
            } else {

                $column = $ORDERBY['column'];
            }

            if(isset($ORDERBY['order'])) {

                $order = $ORDERBY['order'];
            }

            return 'ORDER BY '. $column .' '. $order;
        }
    }




    private function connection($create = null)
    {    
        if(isset($this->settings['name'])) {

            try {

                extract($this->settings);

                return new \PDO('mysql:dbname='. $name .';host='. $host, $user, $password);

            } catch(PDOException $e) {

                exit('Connection Failed: ' . $e->getMessage());
            }
        
        } else {

            exit('Database is not configured.');
        }
    }




    private function condition($args)
    {
        $i = 0;
        $p = [];

        if(isset($args['WHERE']) && !empty($args['WHERE'])) {

            $CONDS = [
                'OR',
                'AND',
                'LIKE'
            ];

            $KEYS = array_intersect(array_keys($args['WHERE']), $CONDS);

            // Map conditions
            foreach($args['WHERE'] as $COND => $value) {

                if(in_array($COND, $CONDS) && count($KEYS) > 0) {

                    if($i !== 0) {

                        $p[] = $COND;
                    }

                    $p[] = $this->parameter($value, $COND);

                    $i++;
                
                } else {

                    // Default parameter with AND condition
                    $p[] = $this->parameter($args['WHERE'], 'AND');
                    
                    if(count($KEYS) == 0) {

                        break;
                    }
                }
            }

            return 'WHERE '. implode(' ', $p);
        }
    }




    private function parameter($args, $cond = '')
    {
        $i = 0;
        $p = [];

        // Abbreviation of operators
        $OPER = [
            'EQUAL' => '=',
            'GREQ'  => '>=',
            'LEEQ'  => '<=',
            'NOEQ'  => '!=',
            'LIKE'  => 'LIKE',
        ];

        foreach($args as $key => $value) {

            if($i !== 0) {

                $p[] = $cond;
            }

            if(is_array($value)) {

                if(is_int($key)) {

                    if(is_array($current = current($value))) {

                        $p[] = key($value) ." ". $OPER[key($current)] ." '". current($current) ."'";

                    } else {

                        $p[] = key($value) ." = '". current($value) ."'";
                    }

                } else {

                    if(isset($OPER[key($value)])) {

                        $p[] = $key ." ". $OPER[key($value)] ." '". current($value) ."'";
                    }
                }

            } else {

                $p[] = $key ." = '". $value . "'";
            }

            $i++;
        }

        return implode(' ', $p);
    }

}