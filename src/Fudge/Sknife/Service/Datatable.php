<?php
namespace Fudge\Sknife\Service;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validation;

/**
 * General handling of datatables requests
 * @author Yohann Marillet
 * @since 23/09/13
 */
class Datatable
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @var Session
     */
    protected $session;

    public function __construct(Container $container)
    {
        $this->request = $container->get('request');
        $this->translator = $container->get('translator');
        $this->session = $container->get('session');
    }

    /**
     * Parse a datatable ajax request and format the result to be able to communicate easily with the model layer
     * @param  array      $definitions column definitions (assigning text labels to request's column IDs)
     * @return array
     * @throws \Exception
     * @author Yohann Marillet
     */
    public function handleRequest(array $definitions)
    {
        $return = ['search'=>[],'order'=>[]];

        $definitionsChecker = function ($id) use ($definitions) {
            if (!isset($definitions[$id])) {
                throw new \Exception('Index #'.$id.' is used in this datatable handler and thus must be specified');
            }
        };

        $nbCols = (int) $this->request->get('iColumns'); //number of columns in the table
        $globalSearch = $this->request->get('sSearch'); //global filter search text
        $start = (int) $this->request->get('iDisplayStart'); //starting display row (== page * number of rows - 1)
        $length = (int) $this->request->get('iDisplayLength'); //number of rows per page
        $nbColsSorted = (int) $this->request->get('iSortingCols'); //number of sorted columns

        $validator = Validation::createValidator();
        $errors = $validator->validateValue($nbCols, new Assert\GreaterThanOrEqual(['value'=>0]));
        $validator->validateValue($start, new Assert\GreaterThanOrEqual(['value'=>0]))->addAll($errors);
        $validator->validateValue($length, new Assert\GreaterThanOrEqual(['value'=>1]))->addAll($errors);
        $validator->validateValue($nbColsSorted, new Assert\GreaterThanOrEqual(['value'=>1]))->addAll($errors);
        $validator->validateValue($nbColsSorted, new Assert\LessThan(['value'=>$nbCols]))->addAll($errors);

        if ($errors->count() == 0) {
            $return = $return + ['offset' => $start, 'limit' => $length];
            $return['globalSearch'] = $globalSearch;
            $return['globalSearchColumns'] = [];

            for ($i=0;$i<=$nbCols;$i++) {
                $search = $this->request->get('sSearch_'.$i); //column's search filter content
                if ("true" === $search) {
                    $search = 1;
                } elseif ("false" === $search) {
                    $search = 0;
                }
                $searchable = $this->request->get('bSearchable_'.$i); //was this column searchable ? (for global search)

                if (!empty($search) || $search === 0) {
                    $definitionsChecker($i);
                    $token = isset($definitions[$i]['value'])?$definitions[$i]['value']:$definitions[$i];
                    if (isset($definitions[$i]['value'])) {
                        $item = ['value'=>$search];
                        if (isset($definitions[$i]['type'])) {
                            $item['type'] = $definitions[$i]['type'];
                        }
                        $searchList = [$item];
                    } else {
                        $searchList = [$search];
                    }
                    $return['search'][$token] = $searchList;
                }

                if (!empty($globalSearch) && 'true'==$searchable && (is_scalar($definitions[$i]) || in_array($definitions[$i]['type'], ['scalar','array']))) {
                    $definitionsChecker($i);
                    $return['globalSearchColumns'][] = $definitions[$i];
                }
            }

            for ($i=0;$i<$nbColsSorted;$i++) {
                $sortColumn = (int) $this->request->get('iSortCol_'.$i); //we're on a sorted column: get the column id
                $sortDirection = strtoupper($this->request->get('sSortDir_'.$i)); //sort direction
                $sortable = $this->request->get('bSortable_'.$sortColumn); //was this column sortable ?

                if ('false' == $sortable || empty($sortable)) {
                    continue;
                }

                $validator->validateValue($sortColumn, new Assert\GreaterThanOrEqual(['value'=>0]))->addAll($errors);
                $validator->validateValue($sortColumn, new Assert\LessThan(['value'=>$nbCols]))->addAll($errors);
                $validator->validateValue($sortDirection, new Assert\Choice(['ASC','DESC']))->addAll($errors);

                $definitionsChecker($sortColumn);

                $token = isset($definitions[$sortColumn]['value'])?$definitions[$sortColumn]['value']:$definitions[$sortColumn];
                $return['order'][$token] = $sortDirection;
            }
        }

        if ($errors->count() > 0) {
            $return = [];
            $return['errors'] = $errors;
        }

        return $return;
    }

    /**
     * Format a data source into a Datatable parsable representation
     * @param  array $data
     * @param  int   $nbTotalRecords
     * @param  int   $nbTotalDisplayRecords
     * @return array
     * @author Yohann Marillet
     */
    public function formatResponse($data, $nbTotalRecords, $nbTotalDisplayRecords, $nbSelected=null)
    {
        $return = [];

        $sEcho = (int) $this->request->get('sEcho'); //unique ajax request identifier

        $validator = Validation::createValidator();
        $errors = $validator->validateValue($sEcho, new Assert\GreaterThanOrEqual(['value'=>1]));

        $return['sEcho'] = $sEcho;
        $return['iTotalDisplayRecords'] = $nbTotalDisplayRecords;
        $return['iTotalRecords'] = $nbTotalRecords;
        if (null !== $nbSelected) {
            $return['iSelected'] = $nbSelected;
        }

        $return['aaData'] = $data;

        if ($errors->count() > 0) {
            $return = [];

            /**
             * @var $e ConstraintViolation
             */
            foreach ($errors as $e) {
                $return['errors'][] = $e->getMessage();
            }

        }

        return $return;
    }

    /**
     * Adds a success message in the flashbag to indicate how many elements are selected
     * @param mixed $tableId
     * @author Yohann Marillet
     */
    public function addCountSelectedFlashMessage($tableId)
    {
        $selected = $this->getSelected($tableId);
        $nb = count($selected);
        $message = '{0} Aucun élément n\'est sélectionné|{1} 1 élément est sélectionné|]1,Inf] %nb% éléments sont sélectionnés';
        $this->session->getFlashBag()->add('success',  $this->translator->transChoice($message, $nb, ['%nb%' => $nb]));
    }

    /**
     * Gets the selected items identifiers for a table
     * @param  mixed $tableId
     * @return array list of the ids selected
     * @author Yohann Marillet
     */
    public function getSelected($tableId)
    {
        return $this->session->get($tableId.'_selected', []);
    }

    /**
     * Gets the selected items count for a table
     * @param  mixed $tableId
     * @return int
     * @author Yohann Marillet
     */
    public function countSelected($tableId)
    {
        $return = count($this->getSelected($tableId));

        return $return;
    }

    /**
     * Returns if an element is currently selected int the table
     * @param  mixed $tableId
     * @param  mixed $val
     * @return bool
     * @author Yohann Marillet
     */
    public function isSelected($tableId, $val)
    {
        return isset($this->getSelected($tableId)[$val]);
    }

    /**
     * Add elements in the selected array
     * @param  mixed       $tableId
     * @param  array|mixed $items
     * @return array       the list of selected elements
     * @author Yohann Marillet
     */
    public function addSelected($tableId, $items)
    {
        if (!is_array($items)) {
            $items = [$items=>$items];
        }
        $items = array_combine($items, $items);
        $selected = $this->getSelected($tableId);
        $newSelected = $selected+$items;
        $this->session->set($tableId.'_selected', $newSelected);

        return $this->getSelected($tableId);
    }

    /**
     * Remove elements in the selected array
     * @param  mixed       $tableId
     * @param  array|mixed $items
     * @return array       the list of selected elements
     * @author Yohann Marillet
     */
    public function removeSelected($tableId, $items)
    {
        if (!is_array($items)) {
            $items = [$items];
        }
        $items = array_combine($items, $items);
        $selected = $this->getSelected($tableId);
        $intersect = array_intersect_key($selected,$items);
        $newSelected = array_diff_key($selected,$intersect);
        $this->session->set($tableId.'_selected', $newSelected);

        return $this->getSelected($tableId);
    }

    /**
     * Unselect all the elements
     * @var mixed $tableId
     * @author Yohann Marillet
     */
    public function clearSelected($tableId)
    {
        $this->session->set($tableId.'_selected', []);
    }
}
