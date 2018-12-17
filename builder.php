<?php


class QueryFieldFilter {
    public $fieldName;
    public $value;

    public function __construct ($fieldName, $value) {
        $this->fieldName = $fieldName;
        $this->value = $value;
    }
}

class QueryCondition {

}


class QueryCombination {
    public $combinations = array();
    public $filters = array();
    public $conditions = array();
    public $type;

    public function __construct ($type, ...$criterias) {
        $this->type = $type;
        if (sizeof($criterias) < 2) {
            throw new Exception("at least 2 criterias need to be provided");
        }

        foreach ($criterias as $criteria) {
            if ($criteria instanceof QueryFieldFilter) {
                array_push($this->filters, $criteria);
            }
            if ($criteria instanceof QueryCondition) {
                array_push($this->conditions, $criteria);
            }
        }
    }

    public function addCombination($combination) {
        array_push($this->combinations, $combination);
        return $this;
    }
}

abstract class Conditions {
    const GreaterThan = '$gt';
    const GreaterThanOrEqual = '$gte';
    const LowerThan = '$lt';
    const LowerThanOrEqual = '$lte';
    const Equal = '$eq';
    const NotEqual = '$neq';
    const Exists = '$exists';
    const Size = '$size';
    const In = '$in';
    const Type = '$type';
    const Regex = '$regex';
    const Mod = '$mod';
}

abstract  class Combinations {
    const And = '$and';
    const Or = '$or';
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
    private $combinations = array();


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
            $len = sizeof($conditions);
            if ($len > 0) {
                $properties = array_keys($conditions);
                for ($i = 0; $i < $len ; $i++) {
                    $selector->{$properties[$i]} = $conditions{$properties[$i]};
                }
            }

            // combinations
            if (sizeof($this->combinations) > 0) {
               foreach ($this->combinations as $combination) {
                   $this->addCombinationToRoot($selector, $combination);
               }
		    }
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
        array_push($this->filters, new QueryFieldFilter($fieldName, $value));
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


    function addCombination($combination) {
        array_push($this->combinations, $combination);
        return $this;
    }

    private function addCombinationToRoot($root, $combination) {
        echo "addCombinationToRoot";
    }

    private function addCombinationToParent() {

    }
}

$queryBuilder = new QueryBuilder();


$combination = new QueryCombination(Combinations::And, new QueryFieldFilter("a", 1), new QueryFieldFilter("b", 2));
$json = $queryBuilder
    ->addField("id", "name", "testing")
    ->setLimit(20)
    ->setSkip(20)
    ->addSort("id", "asc")
    ->addFilter("filterField", array(1,2,3))
    ->addCondition(Conditions::Regex, "id","fefee")
    ->addCondition(Conditions::Mod, 'fefefe', [1,2])
    ->addCondition(Conditions::In, "fullName", [1,2,3,4])
    ->addCombination($combination)
    ->build();
echo $json;
