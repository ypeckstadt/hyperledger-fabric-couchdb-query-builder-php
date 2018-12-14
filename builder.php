<?php


class QueryFilter {
    public $fieldName;
    public $value;

    public function __construct ($fieldName, $value) {
        $this->fieldName = $fieldName;
        $this->value = $value;
    }
}

abstract class Conditions
{
    const GreaterThan = '$gt';
    const GreaterThanOrEqual = '$gte';
    const LowerThan = '$lt';
    const LowerThanOrEqual = '$lte';
}


class QueryBuilder {

    private $fields = array();
    private $hasSelector;
    private $docType = "";
    private $limit;
    private $skip;
    private $sort;
    private $filters = array();
    private $conditions;


    public function __construct () {
       $this->sort = new stdClass();
       $this->conditions = new stdClass();
    }

    function build() {
        if (!$this->hasSelector) {
            throw new Exception("No doctype or filters have been added for the selector.");
        }

        // Initial declaration
        $query = new stdClass();

        // add fields
        if (sizeof($this->fields) > 0) {
            $query->fields = $this->fields;
        }

        // add selector
        if ($this->hasSelector) {
            $query->selector = new stdClass();
            $selector = $query->selector;

            // add doc type
            if (!empty($this->docType)) {
                $selector->docType = $this->docType;
            }

            // add filters
            if (sizeof($this->filters) > 0) {
                foreach ($this->filters as $queryFilter) {
                    $fieldName = $queryFilter->fieldName;
                    $selector->$fieldName = $queryFilter->value;
                }
            }

            // add conditions
            $conditions = get_object_vars($this->conditions);
            if (sizeof($conditions) > 0) {
                echo $conditions;
//                $i = 0;
//                foreach ($conditions as $condition) {
//                    $selector->{$condition} = "dwfwfwdwdw";
//                    $i++;
//                }
            }

            // combinations
        }

        // add sort
        if (sizeof(get_object_vars($this->sort)) > 0) {
            $query->sort = $this->sort;
        }

        // add limit
        if (!empty($this->limit)) {
            $query->limit = $this->limit;
        }


        // add skip
        if (!empty($this->skip)) {
            $query->skip = $this->skip;
        }


        $json = json_encode($query);
        return  $json;
    }

    function addField(...$field) {
        foreach ($field as &$value) {
            array_push($this->fields,$value);
        }
        return $this;
    }

    function addFilter($fieldName, $value) {
        array_push($this->filters, new QueryFilter($fieldName, $value));
        $this->hasSelector = TRUE;
        return $this;
    }

    function setDoctype($docType) {
        $this->docType = $docType;
        $this->hasSelector = TRUE;
        return $this;
    }

    function setLimit($limit) {
        $this->limit = $limit;
        return $this;
    }

    function setSkip($skip) {
        $this->skip = $skip;
        return $this;
    }

    function addCondition($conditionType, $field, $value) {
        $condition = new stdClass();
        $condition->{$conditionType} = $value;
        $this->conditions->{$field} = $condition;
        $this->hasSelector = TRUE;
        return $this;
    }

    function addSort($field, $sortOrder) {
        $this->sort->$field = $sortOrder;
        return $this;
    }

    function addCombination() {

    }

    private function addCombinationToRoot() {

    }

    private function addCombinationToParent() {

    }
}

$queryBuilder = new QueryBuilder();
$json = $queryBuilder
    ->addField("id", "name", "testing")
    ->setLimit(20)
    ->setSkip(20)
    ->addSort("id", "asc")
    ->addFilter("filterField", array(1,2,3))
    ->addCondition(Conditions::GreaterThan, "id",1)
    ->build();
echo $json;
