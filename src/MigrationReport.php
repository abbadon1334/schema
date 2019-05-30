<?php

namespace atk4\schema;

/**
 * Class MigrationChange
 */
class MigrationReport
{
    const REPORT_TYPE_NULL   = 0;
    const REPORT_TYPE_SIMPLE = 1;
    const REPORT_TYPE_DETAIL = 2;
    const REPORT_TYPE_ARRAY  = 3;

    /**
     * @var int
     */
    private $report_type;

    /**
     * Track if change
     *
     * @var bool
     */
    private $changed = false;
    /**
     *
     * @var array<string,array>
     */
    private $added = [];
    /**
     * @var array<string,array>
     */
    private $dropped = [];
    /**
     * @var array<string,array>
     */
    private $altered = [];

    /**
     * Table name
     *
     * @var string
     */
    private $table;

    /**
     * Old Table name
     * @TODO Track Table rename - needed
     *
     * @var string
     */
    private $old_table;

    /**
     * Migration Object
     * @var Migration
     */
    private $migration;

    /**
     * MigrationReport constructor.
     *
     * @param Migration $migration
     * @param int       $report_type
     */
    public function __construct($migration, $report_type = self::REPORT_TYPE_SIMPLE)
    {
        $this->migration   = $migration;
        $this->table       = $migration['table'];
        $this->old_table   = $migration['old_table'] ?? null;
        $this->report_type = $report_type;
    }

    /**
     * @param string $fieldName
     * @param array  $options
     */
    public function addFieldAdded($fieldName, $options)
    {
        $this->added[$fieldName] = $options;
        $this->changed           = true;
    }

    /**
     * @param string $fieldName
     * @param array  $options
     * @param array  $oldOptions
     */
    public function addFieldAltered($fieldName, $options, $oldOptions)
    {
        $this->altered[$fieldName] = ['old' => $oldOptions, 'new' => $options];
        $this->changed             = true;
    }

    /**
     * @param string $fieldName
     * @param array  $options
     */
    public function addFieldDropped($fieldName, $options)
    {
        $this->dropped[$fieldName] = $options;
        $this->changed             = true;
    }

    /**
     * @return bool
     */
    public function hasChanges()
    {
        return $this->changed;
    }

    /**
     * Return a report
     *
     * @return array|string|null
     */
    public function report()
    {
        switch ($this->report_type) {
            case self::REPORT_TYPE_NULL:
                return null;
                break;

            case self::REPORT_TYPE_SIMPLE:
                return $this->getReportSimple();
                break;

            case self::REPORT_TYPE_DETAIL:
                return $this->getReportDetailed();
                break;

            case self::REPORT_TYPE_ARRAY:
                return $this->getReportAsArray();
                break;
        }
    }

    /**
     * @return string
     */
    private function getReportSimple()
    {
        $output   = [];
        $output[] = 'ON TABLE : ' . $this->table . ' => ';

        if (!$this->changed) {
            $output[] = 'no changes';
        } else {
            $count    = count($this->added);
            $output[] = 'added ' . $count . ' field' . ($count === 1 ? '' : 's') . ', ';
            $count    = count($this->altered);
            $output[] = 'altered ' . $count . ' field' . ($count === 1 ? '' : 's') . ' and ';
            $count    = count($this->dropped);
            $output[] = 'dropped ' . $count . ' field' . ($count === 1 ? '' : 's');
        }

        return implode('', $output);
    }

    /**
     * @return string
     */
    private function getReportDetailed()
    {

        $output   = [];

        $output[] = 'TABLE : ' . $this->table;
        $output[] = '################################';

        if (!$this->changed) {
            $output[] = 'no changes';
        } else {

            $output[] = 'ADDED (' . count($this->added) . ')';
            foreach ($this->added as $fieldName => $options) {
                $output[] = $fieldName;
            }

            $output[] = 'ALTERED (' . count($this->altered) . ')';
            foreach ($this->altered as $fieldName => $options) {
                $oldType  = $options['old']['type'] ?? 'NULL';
                $newType  = $options['new']['type'] ?? 'NULL';
                $output[] = $fieldName . ' Type : ' . $oldType . ' => ' . $newType;
            }

            $output[] = 'DROPPED (' . count($this->dropped) . ')';
            foreach ($this->dropped as $fieldName => $options) {
                $output[] = $fieldName;
            }
        }

        return implode(PHP_EOL, $output);
    }

    /**
     * @return array
     */
    private function getReportAsArray()
    {
        return [
            'added'   => $this->added,
            'altered' => $this->altered,
            'dropped' => $this->dropped
        ];
    }
}