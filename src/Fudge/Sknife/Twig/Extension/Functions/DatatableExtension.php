<?php
namespace Fudge\Sknife\Twig\Extension\Functions;

/**
 * Datatable function for twig
 * @author Yohann Marillet <yohann.marillet@gmail.com>
 * @since 19/09/13
 */
class DatatableExtension extends \Twig_Extension
{
    /**
     * Automatically save the datatable parameters for reuse by id (example: to generate it once, then output JS later with the same options as initially passed)
     * @var array
     */
    private static $registered_elements = array();

    /**
     * @var \Twig_Environment
     */
    private $environment;

    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return array(
            'datatable' => new \Twig_SimpleFunction('datatable', array($this, 'datatable'), array('is_safe' => ['html'])),
        );
    }

    /**
     * Returns a datatable (jQuery plugin) html and/or js
     * Options available:
     *      - (string) template: symfony style template name. Defaults to SknifeBundle:Datatable:[template_name].html.twig
     *      - (string) template_name: name of the template (used in [template] parameter)
     *      - (string|array) type: 'lib_css', 'lib_js', 'html', 'js' or combination of these types or empty (init data)
     *      - (array) columns: column names and parameters
     *          - can be single dimensional, in that case the scalar value will be the name of the column
     *          - can be double dimensional, where these parameters apply:
     *              - (string) name: label of the column
     *              - (bool|string) sortable: click on label will make the datable to sort differently (asc/dec). Can define a specific rule of sorting through a string. defaults to true.
     *              - (bool) selectable: will display a dropdown with all the different values available on the column on top of the label. defaults to false. cannot be used with filterable (this one takes precedence)
     *              - (bool|array) filterable: will display a search box (if true) or a select2 search box (if array) on top of the label. defaults to false.
     *      - (array) data: rows data, ordered by column names
     *      - (array) plugins: names or paths to custom plugins to use
     *      - (array) customOptions: custom options to pass to the datatable JS constructor
     *      - (array) groupedActions: info needed for the grouped actions
     *          - (array)
     *              - identifier => {(string) label: label of the grouped action, (string) action: url of the grouped action}
     *
     * @param  string     $id      unique html ID for the table - the function stores internally most of the options passed to an ID
     * @param  array      $options
     * @return string
     * @throws \Exception
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function datatable($id, Array $options=array())
    {
        $options['id'] = strval($id);

        if (!isset(self::$registered_elements[$options['id']])) {
            self::$registered_elements[$options['id']] = array();
        }

        $options = $options + self::$registered_elements[$options['id']];

        if (!isset($options['template'])) {
            $options['template'] = 'SknifeBundle:Datatable:%s.html.twig';
        }

        if (!isset($options['template_name'])) {
            $options['template_name'] = 'default';
        }

        if (!isset($options['type'])) {
            $options['type']=array();
        }

        if (!is_array($options['type'])) {
            $options['type'] = array($options['type']);
        }

        if (in_array('html', $options['type'])) {
            if (!isset($options['columns'])) {
                throw new \Exception('You didn\'t specify the columns to render ($options[\'columns\'])');
            } else {
                $options['aoColumns'] = array();
                foreach ($options['columns'] as $i) {
                    $aoColumns = array();
                    if (isset($i['groupedActions']) || (isset($i['sortable']) && !$i['sortable'])) {
                        $aoColumns['bSortable'] = false;
                    } elseif (isset($i['sort'])) {
                        $aoColumns['sType'] = $i['sort'];
                    }

                    if (isset($i['groupedActions']) || (!isset($i['filterable']) && !isset($i['selectable']))) {
                        $aoColumns['bSearchable'] = false;
                    }

                    if (isset($i['dataClasses'])) {
                        $aoColumns['sClass'] = $i['dataClasses'];
                    }

                    $options['aoColumns'][] = empty($aoColumns)?null:$aoColumns;
                }
            }

            if (!isset($options['data'])) {
                throw new \Exception('You didn\'t specify the data to render ($options[\'data\'])');
            }
        }

        if (!isset($options['customOptions'])) {
            $options['customOptions'] = array();
        }

        $options_to_save = $options;
        unset($options_to_save['type']);
        unset($options_to_save['id']);

        // update the static var with the new params
        self::$registered_elements[$options['id']] = $options_to_save;

        $template = sprintf($options['template'], $options['template_name']);

        return $this->environment->render($template,array('datatable'=>$options));
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'sknife_datatable';
    }
}
