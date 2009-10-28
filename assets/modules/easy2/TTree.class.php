<?php

  /*
   ****************************************************************************
   *   TraversalTree 1.0 (19/07/2006)                                         *
   *                                                                          *
   *   Автор Андрей Яковлев (inteldesign@mail.ru)                             *
   *   PHP Класс для работы с траверсными деревьями                           *
   *
   *   SQL

   CREATE TABLE `catalog` (
     `parent_id` int(10) unsigned NOT NULL default '0',
     `cat_id` int(10) unsigned NOT NULL auto_increment,
     `cat_left` int(10) unsigned NOT NULL default '0',
     `cat_right` int(10) unsigned NOT NULL default '0',
     `cat_level` int(10) unsigned NOT NULL default '0',
     `cat_name` varchar(255) NOT NULL default '',
     `cat_visible` tinyint(4) NOT NULL default '1',
     PRIMARY KEY  (`cat_id`),
     KEY `cat_left` (`cat_left`)
   ) TYPE=MyISAM AUTO_INCREMENT=2 ;

   INSERT INTO catalog VALUES (0, 1, 1, 2, 0, '');

   *
   ****************************************************************************
  */

  class TTree {

      var $table;
      var $parent = 'parent_id';
      var $id     = 'cat_id';
      var $left   = 'cat_left';
      var $right  = 'cat_right';
      var $level  = 'cat_level';
      var $name   = 'cat_name';
      var $error;

      // добавление ветви

      function insert ($name, $parent_id = 1)
      {

          // Родительский каталог

          $query = 'SELECT * FROM ' . $this->table . ' WHERE '
                 . $this->id . ' = '.$parent_id;
          $result = mysql_query($query);
          if (mysql_error()) {
              $this->error = '#'.mysql_errno().' '.mysql_error();
              return false;
          } elseif (mysql_num_rows($result) == 0) {
              $this->error = 'Parent ID not found (INSERT)';
              return false;
          }
          $parent = mysql_fetch_array($result, MYSQL_ASSOC);


          $query = 'UPDATE ' . $this->table . ' SET '
                 . $this->left . ' = '
                 . 'IF(' . $this->left . ' > ' . $parent[$this->left] . ','
                 . $this->left . ' + 2, ' . $this->left . '), '
                 . $this->right . ' = '
                 . 'IF(' . $this->right . ' >= ' . $parent[$this->right] . ','
                 . $this->right . ' + 2, ' . $this->right . ') '
                 . 'WHERE ' . $this->right . ' >= ' . $parent[$this->right];

          if (!mysql_query($query)) {
              $this->error = '#'.mysql_errno().' '.mysql_error();
              return false;
          }


          $query = 'INSERT INTO ' . $this->table . ' ('
                 . $this->parent . ', '
                 . $this->left . ', '
                 . $this->right . ', '
                 . $this->level . ', '
                 . $this->name . ') '
                 . 'VALUES ( '
                 . $parent_id . ', '
                 . $parent[$this->right] . ', '
                 . ($parent[$this->right] + 1) . ', '
                 . ($parent[$this->level] + 1) . ', '
                 . '\'' . mysql_real_escape_string($name) . '\' )';


          if (mysql_query($query)) {
              return mysql_insert_id();
          } else {
              $this->error = '#'.mysql_errno().' '.mysql_error();
              return false;
          }
      }

      // изменение ветви

      function update ($id, $name, $parent_id = false)
      {

          if (empty($id) || !is_numeric($id)) {
              $this->error = 'Identifier error (UPDATE)';
              return false;
          }


          if (!empty($parent_id)) {

              // Проверка на изменение родительского каталога

              $query = 'SELECT * FROM ' . $this->table . ' WHERE '
                     . $this->id . ' = ' . $id;

              $result = mysql_query($query);
              $c = mysql_fetch_array($result, MYSQL_ASSOC);

              if ($c[$this->parent] != $parent_id) {
                  if (!$this->replace ($id, $parent_id, $c)) return false;
              }
          }


          $query = 'UPDATE ' . $this->table
                 . ' SET '
                 . $this->name . ' = \'' . mysql_real_escape_string($name) . '\' '
                 . ' WHERE ' . $this->id . ' = ' . $id;

          if (mysql_query($query)) {
              return true;
          } else {
              $this->error = '#'.mysql_errno().' '.mysql_error();
              return false;
          }
      }

      // Перемещение ветви

      function replace ($id, $to_id, $c = false)
      {

          if (!is_numeric($id) || !is_numeric($to_id)) {
              $this->error = 'Identifier error (REPLACE)';
              return false;
          }

          // Если данные каталога не переданы по ссылке
          if (!$c) {
              $query = 'SELECT * FROM ' . $this->table . ' WHERE '
                     . $this->id . ' = ' . $id;

              $result = mysql_query($query);
              $c = mysql_fetch_array($result, MYSQL_ASSOC);
          }


          // Переносимая ветка * (-1)
          $query = 'UPDATE ' . $this->table . ' SET '
                 . $this->left . ' = ' . $this->left . ' * (-1) '
                 . 'WHERE '
                 . $this->left . ' BETWEEN '
                 . $c[$this->left] . ' AND ' . $c[$this->right];

          if (!mysql_query($query)) {
              $this->error = '#'.mysql_errno().' '.mysql_error();
              return false;
          }

          // Данные о новой родительской ветви
          $query = 'SELECT * FROM ' . $this->table
                 . ' WHERE ' . $this->id . ' = ' . $to_id;

          if (!($result = mysql_query($query))) {
              $this->error = '#'.mysql_errno().' '.mysql_error();
              return false;
          }
          $to = mysql_fetch_array($result, MYSQL_ASSOC);

          if ($to[$this->left] > $c[$this->left] && $to[$this->left] < $c[$this->right]){
              $this->error = 'Parent not parent';
              return false;
          }

          // Разность Л и Р переносимой ветки
          $razn = $c[$this->right] - $c[$this->left] + 1;

          // Определение операции +/- над новой род. веткой
          if ($c[$this->left]<$to[$this->right] && $c[$this->left]>$to[$this->left]) {
              $query = 'UPDATE ' . $this->table . ' SET '
                 . $this->right . ' = '
                 . 'IF (' . $this->right . ' - ' . $this->left . ' = 1, '
                 . $this->right . '+' . $razn . ', ' . $this->right . '), '
                 . $this->left . ' = ' . $this->left . '+' . $razn . ' '
                 . ' WHERE ' . $this->left . ' > ' . $to[$this->left]
                 . ' AND ' . $this->left . ' < ' . $c[$this->left];

              $razn2 = $to[$this->left] - $c[$this->left] + 1;
          } elseif ($c[$this->left] < $to[$this->right]) {

              $query = 'UPDATE ' . $this->table . ' SET '
                 . $this->left . ' = '
                 . 'IF(' . $this->left . ' > ' . $c[$this->left] . ', '
                 . $this->left . '-' . $razn . ', ' . $this->left . '), '
                 . $this->right . ' = '
                 . 'IF(' . $this->right . ' < ' . $to[$this->right] . ', '
                 . $this->right . '-' . $razn . ', ' . $this->right . ') '
                 . ' WHERE (' . $this->left . ' > ' . $c[$this->left]
                 . ' AND ' . $this->left . ' <= ' . $to[$this->left] . ')'
                 . ' OR ' . $this->id . ' = ' . $c[$this->parent];

              $razn2 = $to[$this->left] - $razn - $c[$this->left] + 1;
          } else {
              $query = 'UPDATE ' . $this->table . ' SET '
                 . $this->left . ' = '
                 . 'IF(' . $this->left . ' > ' . $to[$this->left] . ', '
                 . $this->left . '+' . $razn . ', ' . $this->left . '), '
                 . $this->right . ' = '
                 . 'IF(' . $this->right . ' < ' . $c[$this->left] . ', '
                 . $this->right . '+' . $razn . ', ' . $this->right . ') '
                 . ' WHERE ' . $this->left . ' >= ' . $to[$this->left]
                 . ' AND ' . $this->left . ' < ' . $c[$this->left];

              $razn2 = $to[$this->left] - $c[$this->left] + 1;
          }
          // $razn2 - разность между родительской веткой и переносимой


          echo $query .'<br />';

          if (!mysql_query($query)) {
              $this->error = '#'.mysql_errno().' '.mysql_error();
              return false;
          }


          //$razn = $to[$this->right] - $c[$this->left];
          $lev  = $to[$this->level] - $c[$this->level] + 1;
          $query = 'UPDATE ' . $this->table . ' SET '
                 . $this->left . ' = ' . $this->left . '*(-1)+' . $razn2 . ', '
                 . $this->right . ' = ' . $this->right . '+' . $razn2 . ', '
                 . $this->level . ' = ' . $this->level . '+' . $lev . ', '
                 . $this->parent . ' = '
                 . 'IF(' . $this->id . '=' . $id . ', '
                 . $to[$this->id] . ', ' . $this->parent . ') '
                 . 'WHERE ' . $this->left . ' < 0';

          if (mysql_query($query)) {
              return true;
          } else {
              $this->error = '#'.mysql_errno().' '.mysql_error();
              return false;
          }

      }

      // Удаление ветви

      function delete ($id)
      {
          $query = 'SELECT * FROM ' . $this->table . ' WHERE ' . $this->id . ' = '.$id;
          $result = mysql_query($query);

          if (mysql_error()) {
              $this->error = '#'.mysql_errno().' '.mysql_error();
              return false;
          } elseif (mysql_num_rows($result) == 0) {
              $this->error = 'Catalog not found!';
              return false;
          }

          $cat = mysql_fetch_array($result, MYSQL_ASSOC);

          // Список вложенний
          $query = 'SELECT ' . $this->id . ' FROM ' . $this->table
                 . ' WHERE ' . $this->left
                 . ' BETWEEN ' . $cat[$this->left] . ' AND ' . $cat[$this->right];
          $ids = $this->sql2array($query, $this->id);

          // Удаление
          $query = 'DELETE FROM ' . $this->table
                 . ' WHERE ' . $this->left
                 . ' BETWEEN ' . $cat[$this->left] . ' AND ' . $cat[$this->right];

          if (mysql_query($query)) {
              return $ids;
          } else {
              $this->error ='#'.mysql_errno().' '.mysql_error();
              return false;
          }
      }

      // удаление дерева

      function clear ()
      {

          if (!mysql_query('DELETE FROM ' . $this->table)) {
              $this->error ='#'.mysql_errno().' '.mysql_error();
              return false;
          }

          $query = 'INSERT INTO ' . $this->table . ' ( '
                 . $this->parent . ', '
                 . $this->id . ', '
                 . $this->left . ', '
                 . $this->right . ', '
                 . $this->level . ', '
                 . $this->name . ') VALUES (0, 1, 1, 10, 0, \'\')';
          /*,(1, 2, 2, 5, 1, \'test\'),(2, 3, 3, 4, 2, \'new4\'),
          (1, 4, 6, 9, 1, \'test2\'),(4, 5, 7, 8, 2, \'new2\')*/

          if (mysql_query($query)) {
              return true;
          } else {
              $this->error ='#'.mysql_errno().' '.mysql_error();
              return false;
          }
      }

      // Каталог в массив

      function catalog2array ()
      {
         $query = 'SELECT * FROM ' . $this->table . ' ORDER BY ' . $this->left;
         return $this->sql2array($query, $this->id);
      }

      function sql2array($sql, $keyField='') {
          if( (empty($sql)) || (!($query = mysql_query($sql))) ) return false;
          if(mysql_num_rows($query) < 1) return false;
          return $this->result2array($query, $keyField);
      }

      function result2array($q, $keyField='') {
          $Result = array();
          while($Data = mysql_fetch_array($q, MYSQL_ASSOC))
              if(empty($keyField)) $Result[] = $Data;
              //else $Result[$Data[$keyField]] = $Data;
			  else $Result[] = $Data[$keyField];
          mysql_free_result($q);
          return $Result;
      }
  }


?>