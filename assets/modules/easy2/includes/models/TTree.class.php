<?php

/**
 * TraversalTree 1.0 (19/07/2006)
 *               1.1 (19/05/2010)
 * Автор Андрей Яковлев <inteldesign@mail.ru>
 * @author Andrew Jakovlev
 * @author goldsky <goldsky@fastmail.fm>
 * PHP Класс для работы с траверсными деревьями
 * PHP Class for traversal trees
 *
 * SQL

  CREATE TABLE `catalog` (
  `parent_id` int(10) unsigned NOT NULL default '0',
  `cat_id` int(10) unsigned NOT NULL auto_increment,
  `cat_left` int(10) NOT NULL default '0',           // goldsky -- signed, to have negative value
  `cat_right` int(10) unsigned NOT NULL default '0',
  `cat_level` int(10) unsigned NOT NULL default '0',
  `cat_name` varchar(255) NOT NULL default '',
  `cat_visible` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`cat_id`),
  KEY `cat_left` (`cat_left`)
  ) TYPE=MyISAM AUTO_INCREMENT=2 ;

  INSERT INTO catalog VALUES (0, 1, 1, 2, 0, '');

 *
 * ***************************************************************************
 */
class TTree {

    var $table;
    var $parent = 'parent_id';
    var $id = 'cat_id';
    var $left = 'cat_left';
    var $right = 'cat_right';
    var $level = 'cat_level';
    var $name = 'cat_name';
    var $error;
    var $reports = array(); // goldsky -- added, only for debuging

    // добавление ветви
    // add branch

    function insert($name, $parentId = 1) {

        // Родительский каталог
        // Parrent catalog
        $query = 'SELECT * FROM ' . $this->table . ' WHERE '
                . $this->id . ' = ' . $parentId;
        $result = mysql_query($query);
        if (mysql_error()) {
            $this->error = '#' . mysql_errno() . ' ' . mysql_error();
            return false;
        } elseif (mysql_num_rows($result) == 0) {
            $this->error = 'Parent ID not found (INSERT)';
            return false;
        }
        $parent = mysql_fetch_assoc($result);

        // Update the all root parents' data first.
        $query = 'UPDATE ' . $this->table . ' SET '
                // search LEFT
                . $this->left . ' = '
                . ' if (' . $this->left . ' > ' . $parent[$this->left] . ','
                . $this->left . ' + 2, ' . $this->left . '), '
                // search RIGHT
                . $this->right . ' = '
                . ' if (' . $this->right . ' >= ' . $parent[$this->right] . ','
                . $this->right . ' + 2, ' . $this->right . ') '
                // crawling all parents
                . ' WHERE ' . $this->right . ' >= ' . $parent[$this->right];

        if (!mysql_query($query)) {
            $this->error = '#' . mysql_errno() . ' ' . mysql_error();
            return false;
        }

        // Insert new data
        $query = 'INSERT INTO ' . $this->table . ' ('
                . $this->parent . ', '
                . $this->left . ', '
                . $this->right . ', '
                . $this->level . ', '
                . $this->name . ') '
                . 'VALUES ( '
                . $parentId . ', '
                . $parent[$this->right] . ', '
                . ($parent[$this->right] + 1) . ', '
                . ($parent[$this->level] + 1) . ', '
                . '\'' . mysql_real_escape_string($name) . '\' )';

        if (mysql_query($query)) {
            // Returns a new cat_id
            return mysql_insert_id();
        } else {
            $this->error = '#' . mysql_errno() . ' ' . mysql_error();
            return false;
        }
    }

    // изменение ветви
    // modify/update branch
    function update($id, $name, $parentId = false) {

        if (empty($id) || !is_numeric($id)) {
            $this->error = 'Identifier error (UPDATE)';
            return false;
        }

        if (!empty($parentId)) {

            // Проверка на изменение родительского каталога
            // Check for modification/change of parrent catalog
            $query = 'SELECT * FROM ' . $this->table . ' WHERE '
                    . $this->id . ' = ' . $id;

            $result = mysql_query($query);
            $c = mysql_fetch_assoc($result);

            if ($c[$this->parent] != $parentId) {
                if (!$this->replace($id, $parentId, $c))
                    return false;
            }
        }

        $query = 'UPDATE ' . $this->table
                . ' SET '
                . $this->name . ' = \'' . mysql_real_escape_string($name) . '\' '
                . ' WHERE ' . $this->id . ' = ' . $id;

        if (mysql_query($query)) {
            return true;
        } else {
            $this->error = '#' . mysql_errno() . ' ' . mysql_error();
            return false;
        }
    }

    // Перемещение ветви
    // Move/replace??? branch
    function replace($id, $to_id, $c = false) {

        if (!is_numeric($id) || !is_numeric($to_id)) {
            $this->error = 'Identifier error (REPLACE)';
            return false;
        }

        $this->reports[] = __LINE__ . ' : ' . __METHOD__;

        // ********************************** HANDLERS OF THE SELF parent ********************************** //
        // Если данные каталога не переданы по ссылке
        // if catalog data is not send with link (/reference?)
        // see update() function above.
        if (!$c) {
            $select_c = 'SELECT * FROM ' . $this->table . ' WHERE '
                    . $this->id . ' = ' . $id;
            $result_select_c = mysql_query($select_c);

            // set the old parent's handler variable
            $c = mysql_fetch_assoc($result_select_c);
        }

        // Данные о новой родительской ветви
        // *********************************** HANDLERS OF THE new parent ********************************** //

        $select_to = 'SELECT * FROM ' . $this->table                                     // goldsky
                . ' WHERE ' . $this->id . ' = ' . $to_id;
        if (!($result_select_to = mysql_query($select_to))) {
            $this->error = __LINE__ . ' : #' . mysql_errno() . '<br />';
            $this->error .= __LINE__ . ' : ' . mysql_error() . '<br />';
            $this->error .= __LINE__ . ' : ' . $select_to;
            return false;
        }
        // set the new parent's handler variable
        $to = mysql_fetch_assoc($result_select_to);

        // ***************************************** ERROR HANDLERS **************************************** //

        if ($to[$this->left] > $c[$this->left] && $to[$this->left] < $c[$this->right]) {
            $this->error = __LINE__ . ' : Should not move parent folder
                ( <span style="color:blue;">' . $c[$this->name] . ' [id: ' . $c[$this->id] . ']</span> )
                to its own branch folder
                ( <span style="color:blue;">' . $to[$this->name] . ' [id: ' . $to[$this->id] . ']</span> ).'; // goldsky
            return false;
        }
        // goldsky -- same ID, assume mistype
        if ($to[$this->id] == $c[$this->id]) {
            $this->error = __LINE__ . ' : Could not move folder into itself.';
            return false;
        }
        // goldsky -- same parent ID, assume mistype
        if ($to[$this->id] == $c[$this->parent]) {
            $this->error = __LINE__ . ' : Could not move folder into the same folder.';
            return false;
        }

        // Переносимая ветка * (-1)
        // ********************** SET TEMPORARY cat_left VALUE TO THE moving branches ********************** //

        $update_between_c = 'UPDATE ' . $this->table . ' SET '                           // goldsky
                . $this->left . ' = ' . $this->left . ' * (-1) '
                . ' WHERE '
                . $this->left . ' BETWEEN '
                . $c[$this->left] . ' AND ' . $c[$this->right]
        ;

        if (!mysql_query($update_between_c)) {
            $this->error = __LINE__ . ' : #' . mysql_errno() . '<br />';
            $this->error .= __LINE__ . ' : ' . mysql_error() . '<br />';
            $this->error .= __LINE__ . ' : ' . $update_between_c;
            return false;
        }
        $this->reports[] = __LINE__ . ' : $update_between_c = ' . $update_between_c;

        // Разность Л и Р переносимой ветки
        // ************** SET MARGIN VALUE OF THE moving branches AS THE RENUMBERING CONTROL *************** //
        // Difference???/distance between Left and Right of the branch to be moved
        // margin of the OLD PARENT's cat_right - cat_left,
        // higher margin means :
        //   - higher level, OR
        //   - deeper branch, OR
        //   - un-updated value after data deletion
        $razn = $c[$this->right] - $c[$this->left] + 1;
        $this->reports[] = __LINE__ . ' : $razn = ' . $razn;

        // Определение операции +/- над новой род. веткой
        // ************************************************************************************************* //
        // ****************************                                         **************************** //
        // **************************** OPERATIONS +/- ALL OVER THE new parents **************************** //
        // ****************************                                         **************************** //
        // ************************************************************************************************* //
        // grandchild TO BE child OF THE new parent (MOVING UP)
        if ($c[$this->left] < $to[$this->right] && $c[$this->left] > $to[$this->left]) {
            $querygroup = '1'; // goldsky -- for debugging only

            $query = 'UPDATE ' . $this->table . ' SET '
                    // set the LEFT
                    . $this->left . ' = ' . $this->left . '+' . $razn . ', '
                    // set the RIGHT
                    . $this->right . ' = '
//                    . ' if (' . $this->right . ' - ' . $this->left . ' = 1, '
                    . ' if (' . $this->right . ' < ' . $c[$this->left] . ', '             // goldsky
                    . $this->right . '+' . $razn . ', ' . $this->right . ') '
                    . ' WHERE ' . $this->left . ' > ' . $to[$this->left]
                    . ' AND ' . $this->left . ' < ' . $c[$this->left];

            // ******* SET MARGIN VALUE OF ALL OVER THE old parent's branches AS THE RENUMBERING CONTROL ******* //
            $razn2 = $to[$this->left] - $c[$this->left] + 1;

            $this->reports[] = __LINE__ . ' : $query = ' . $query;
            $this->reports[] = __LINE__ . ' : $razn2 = ' . $razn2;
            $this->reports[] = __LINE__ . ' : $querygroup = ' . $querygroup;
        }
        // MOVING TO THE RIGHT SIDE
        elseif ($c[$this->left] < $to[$this->right]) {
//        elseif ($c[$this->left] < $to[$this->right] && $c[$this->right] < $to[$this->right]) { // goldsky
            $querygroup = '2'; // goldsky -- for debugging only

            $query = 'UPDATE ' . $this->table . ' SET '
                    // set the LEFT
                    . $this->left . ' = '
                    . ' if (' . $this->left . ' > ' . $c[$this->left] . ', '
                    . $this->left . '-' . $razn . ', ' . $this->left . '), '
                    // set the RIGHT
                    . $this->right . ' = '
                    . ' if (' . $this->right . ' < ' . $to[$this->right] . ', '
//                    . $to[$this->right] . '-' . $razn . ', ' . $to[$this->right] . ') '
                    . $this->right . '-' . $razn . ', ' . $this->right . ') '            // goldsky
//                    . ' WHERE (' . $this->left . ' > ' . $c[$this->left]
                    . ' WHERE (' . $this->right . ' > ' . $c[$this->right]               // goldsky
                    . ' AND ' . $this->left . ' <= ' . $to[$this->left] . ')'
//                    . ' OR ' . $this->id . ' = ' . $c[$this->parent]
            ;

            // ******* SET MARGIN VALUE OF ALL OVER THE old parent's branches AS THE RENUMBERING CONTROL ******* //
            $razn2 = $to[$this->left] - $razn - $c[$this->left] + 1;

            $this->reports[] = __LINE__ . ' : $query = ' . $query;
            $this->reports[] = __LINE__ . ' : $razn2 = ' . $razn2;
            $this->reports[] = __LINE__ . ' : $querygroup = ' . $querygroup;
        }
        // MOVING TO THE LEFT SIDE
        else { // $c[$this->left] > $to[$this->right]
            $querygroup = '3'; // goldsky -- for debugging only

            $query = 'UPDATE ' . $this->table . ' SET '
                    // set the LEFT of the NEW PARENT
                    . $this->left . ' = '
                    . ' if (' . $this->left . ' > ' . $to[$this->left] . ', '
                    . $this->left . '+' . $razn . ', ' . $this->left . '), '
                    // set the RIGHT
                    . $this->right . ' = '
                    . ' if (' . $this->right . ' < ' . $c[$this->left] . ', '
                    . $this->right . '+' . $razn . ', ' . $this->right . ') '
                    . ' WHERE (' . $this->left . ' >= ' . $to[$this->left]
                    . ' AND ' . $this->left . ' < ' . $c[$this->left] . ')'
                    . ' OR (' . $this->right . ' >= ' . $to[$this->right]                // goldsky
                    . ' AND ' . $this->right . ' < ' . $c[$this->right] . ')'            // goldsky
            ;

            // ******* SET MARGIN VALUE OF ALL OVER THE old parent's branches AS THE RENUMBERING CONTROL ******* //
            $razn2 = $to[$this->left] - $c[$this->left] + 1;

            $this->reports[] = __LINE__ . ' : $query = ' . $query;
            $this->reports[] = __LINE__ . ' : $razn2 = ' . $razn2;
            $this->reports[] = __LINE__ . ' : $querygroup = ' . $querygroup;
        }
        // $razn2 - разность между родительской веткой и переносимой
        // difference??/distance between the parent and the branch to be moved

        if (!mysql_query($query)) {
            $this->error = __LINE__ . ' : #' . mysql_errno() . '<br />';
            $this->error .= __LINE__ . ' : ' . mysql_error() . '<br />';
            $this->error .= __LINE__ . ' : ' . $query;
            return false;
        }

        // *************************** THE FINAL UPDATE OF THE moving branches ***************************** //
        //$razn = $to[$this->right] - $c[$this->left];
        $lev = $to[$this->level] - $c[$this->level] + 1;
        $this->reports[] = __LINE__ . ' : $lev = ' . $lev;

//        $query = 'UPDATE ' . $this->table . ' SET '
        $update_final = 'UPDATE ' . $this->table . ' SET '
                . $this->left . ' = ' . $this->left . '*(-1)+' . $razn2 . ', '
                . $this->right . ' = ' . $this->right . '+' . $razn2 . ', '
                . $this->level . ' = ' . $this->level . '+' . $lev . ', '
                . $this->parent . ' = '
                . ' if (' . $this->id . '=' . $id . ', '
                . $to[$this->id] . ', ' . $this->parent . ') '
                . ' WHERE ' . $this->left . ' < 0';                     // THE MOVING BRANCH'S SIGNATURE!

        if (mysql_query($update_final)) {
            $this->reports[] = __LINE__ . ' : $update_final = ' . $update_final;
            return true;
        } else {
            $this->error = __LINE__ . ' : #' . mysql_errno() . '<br />';
            $this->error .= __LINE__ . ' : ' . mysql_error() . '<br />';
            $this->error .= __LINE__ . ' : ' . $update_final;
            return false;
        }
        // function replace ($id, $to_id, $c = false)
    }

    // Удаление ветви
    // removal of a branch
    function delete($id) {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE ' . $this->id . ' = \'' . $id . '\'';
        $result = mysql_query($query);

        if (mysql_error()) {
            $this->error = '#' . mysql_errno() . ' ' . mysql_error();
            return false;
        } elseif (mysql_num_rows($result) == 0) {
            $this->error = 'Catalog not found!';
            return false;
        }

        // get the parent's data
        $cat = mysql_fetch_assoc($result);

        // Список вложенний
        // List of nested ???
        $query = 'SELECT ' . $this->id . ' FROM ' . $this->table
                . ' WHERE ' . $this->left
                . ' BETWEEN ' . $cat[$this->left] . ' AND ' . $cat[$this->right];
        $ids = $this->sql2array($query, $this->id);

        // Удаление
        // Removal
        $query = 'DELETE FROM ' . $this->table
                . ' WHERE ' . $this->left
                . ' BETWEEN ' . $cat[$this->left] . ' AND ' . $cat[$this->right];

        // *************************************** RETURNS folder ids ************************************** //

        if (mysql_query($query)) {
            return $ids;
        } else {
            $this->error = '#' . mysql_errno() . ' ' . mysql_error();
            return false;
        }
        // function delete ($id)
    }

    /**
     *
     * @return bool true/false on successful execution
     */
    function reindex() {
        $query = 'SELECT * FROM ' . $this->table;
        $result = mysql_query($query);

        if (mysql_error()) {
            $this->error = '#' . mysql_errno() . ' ' . mysql_error();
            return false;
        } elseif (mysql_num_rows($result) == 0) {
            $this->error = 'Catalog not found!';
            return false;
        }

        // get the parent's data
        $cat = mysql_fetch_assoc($result);

        // ************** SET MARGIN VALUE OF THE deleted branches AS THE RENUMBERING CONTROL ************** //
        $razn = $cat[$this->right] - $cat[$this->left] + 1;
        $this->reports[] = __LINE__ . ' : $razn = ' . $razn;

        // ************************** REINDEX THE cat_left AND cat_right VALUES **************************** //
        $update = 'UPDATE ' . $this->table . ' SET '
                . $this->left . ' = '
                . ' if (' . $this->left . ' > ' . $cat[$this->right] . ' , '
                . $this->left . ' - ' . $razn . ', ' . $this->left . '), '
                . $this->right . ' = '
                . ' if (' . $this->right . ' > ' . $cat[$this->right] . ' , '
                . $this->right . ' - ' . $razn . ', ' . $this->right . ') '
                . ' WHERE ' . $this->right . ' > ' . $cat[$this->right];

        if (!mysql_query($update)) {
            $this->error = '#' . mysql_errno() . ' ' . mysql_error();
            return false;
        }
    }

    // удаление дерева
    // Removal of the tree
    function clear() {

        if (!mysql_query('DELETE FROM ' . $this->table)) {
            $this->error = '#' . mysql_errno() . ' ' . mysql_error();
            return false;
        }

        $query = 'INSERT INTO ' . $this->table . ' ( '
                . $this->parent . ', '
                . $this->id . ', '
                . $this->left . ', '
                . $this->right . ', '
                . $this->level . ', '
                . $this->name . ') VALUES (0, 1, 1, 10, 0, \'\')';
        /* ,(1, 2, 2, 5, 1, \'test\'),(2, 3, 3, 4, 2, \'new4\'),
          (1, 4, 6, 9, 1, \'test2\'),(4, 5, 7, 8, 2, \'new2\') */

        if (mysql_query($query)) {
            return true;
        } else {
            $this->error = '#' . mysql_errno() . ' ' . mysql_error();
            return false;
        }
    }

    // Каталог в массив
    // Catalog to array :-)
    function catalog2array() {
        $query = 'SELECT * FROM ' . $this->table . ' ORDER BY ' . $this->left;
        return $this->sql2array($query, $this->id);
    }

    function sql2array($sql, $keyField='') {
        if (empty($sql) || !($query = mysql_query($sql))) {
            return false;
        }
        if (mysql_num_rows($query) < 1) {
            return false;
        }
        return $this->result2array($query, $keyField);
    }

    function result2array($q, $keyField='') {
        $Result = array();
        while ($Data = mysql_fetch_assoc($q))
            if (empty($keyField))
                $Result[] = $Data;
            //else $Result[$Data[$keyField]] = $Data;
            else
                $Result[] = $Data[$keyField];
        mysql_free_result($q);
        return $Result;
    }

}