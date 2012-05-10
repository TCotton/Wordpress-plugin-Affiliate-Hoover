<?php

/**
 * @author Andy Walpole
 * @date 8/5/2012
 * 
 */

/*#################################################################################################

Copyright 2009 The Trustees of Indiana University

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in
compliance with the License. You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed under the License
is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
implied. See the License for the specific language governing permissions and limitations under the
License.

#################################################################################################*/


class path {
    public $colid;

    public $parent;
    public $name;
    public $children;

    public $branch;
    public $counters;
    public $hastext;

    public $rows;
    public $row; //current row to set values

    function __construct($parent, $name) {
        $this->colid = null;
        $this->branch = false;
        $this->hastext = false;
        $this->counter = 0;
        $this->rows = array();
        $this->row = array();

        $this->parent = $parent;
        $this->name = $name;

        //register myself to parent
        $this->children = array();
        if ($parent !== null) {
            $parent->children[] = $this;
        }
    }
    function getFullPath() {
        $fullname = "";
        if ($this->parent !== null) {
            $fullname .= $this->parent->getFullPath()."/";
        }
        $fullname .= $this->name;
        return $fullname;
    }
    function findChild($name) {
        foreach ($this->children as $child) {
            if ($child->name == $name) return $child;
        }
        return null;
    }
    function analyzeColumn(&$cols, $under_branch = false) {
        if ($under_branch && $this->hastext) {
            $this->colid = sizeof($cols);
            $cols[] = $this;
        }
        foreach ($this->children as $child) {
            if ($this->branch) {
                $under_branch = true;
            }
            $child->analyzeColumn($cols, $under_branch);
        }
    }
    function analyzeBranch() {
        foreach ($this->children as $child) {
            if ($child->counter > 1) {
                $child->branch = true;
            }
            $child->counter = 0;
        }
    }
    function getBranch() {
        if ($this->branch) return $this;
        if ($this->parent !== null) {
            return $this->parent->getBranch();
        }
        return null;
    }

    function getRoot() {
        if ($this->parent === null) return $this;
        return $this->parent->getRoot();
    }

    function closeBranch() {
        //close this branch and send all records to parent branch (or root)
        $parent_branch = $this->parent->getBranch();
        if ($parent_branch === null) {
            $parent_branch = $this->getRoot();
        }

        //merge my row and sub-rows to parent rows.
        if (sizeof($this->rows) == 0) {
            $parent_branch->rows[] = merge_row($parent_branch->row, $this->row);
        } else {
            foreach ($this->rows as $row) {
                $parent_branch->rows[] = merge_row($parent_branch->row, merge_row($this->row, $row));
            }
        }
        //reset this and child rows
        $this->row = array();
        $this->rows = array();
    }

    function output($colnum) {
        //output all rows collected
        foreach ($this->rows as $row) {
            for ($i = 0; $i < $colnum; $i++) {
                if (isset($row[$i])) {
                    $value = "\"".str_replace("\"", "\\\"", $row[$i])."\"";
                    echo $value;
                }
                echo ",";
            }
            echo "\n";
        }
    }
}

function merge_row($row1, $row2) {
    foreach ($row2 as $key => $col) {
        $row1[$key] = $col;
    }
    return $row1;
}

function xml2csv($xml_content) {
    $xml = new XMLReader();
    $xml->XML($xml_content);

    //First pass - discover all path and branch points
    $cols = array();
    $root = new path(null, "root");
    $current = $root;
    while ($xml->read()) {
        if (in_array($xml->nodeType, array(
            XMLReader::TEXT,
            XMLReader::CDATA,
            XMLReader::WHITESPACE,
            XMLReader::SIGNIFICANT_WHITESPACE))) {
            if (trim($xml->value) == "") continue;
            $current->hastext = true;
        }
        if ($xml->nodeType == XMLReader::ELEMENT) {
            $child = $current->findChild($xml->name);
            if ($child !== null) {
                $current = $child;
                $current->counter++;
            } else {
                //brand new path
                $current = new path($current, $xml->name);
            }
        }
        if ($xml->nodeType == XMLReader::END_ELEMENT) {
            $current->analyzeBranch();
            $current = $current->parent;
        }
    }

    //output column headder
    $cols = array();
    $root->analyzeColumn($cols);
    foreach ($cols as $path) {
        //append parent's path name to be more descriptive
        if ($path->parent !== null) {
            echo $path->parent->name."/";
        }
        echo $path->name;
        echo ",";
    }
    echo "\n";

    //Second pass - map values to current branch points
    $xml->XML($xml_content);
    $current = $root;
    $branch = null;
    while ($xml->read()) {
        if (in_array($xml->nodeType, array(
            XMLReader::TEXT,
            XMLReader::CDATA,
            XMLReader::WHITESPACE,
            XMLReader::SIGNIFICANT_WHITESPACE))) {
            $value = trim($xml->value);
            if (trim($xml->value) == "") continue;
            $branch->row[$current->colid] = $value;
        }
        if ($xml->nodeType == XMLReader::ELEMENT) {
            $current = $current->findChild($xml->name);
            $branch = $current->getBranch();
        }
        if ($xml->nodeType == XMLReader::END_ELEMENT) {
            if ($current == $branch) {
                $branch->closeBranch();
            }
            $current = $current->parent;
            $branch_new = $current->getBranch();
            if ($branch_new !== null) {
                $branch = $branch_new;
            }
        }
    }

    //dump the content
    $root->output(sizeof($cols));
}

$convert = new path(null,"newfile.csv");

//var_dump($convert->xml2csv());


?>