<?php
/**
 * Copyright (c) 2019.
 *
 * Francesco "Abbadon1334" Danti <fdanti@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 */

namespace atk4\schema\Migration;

class MySQL extends \atk4\schema\Migration
{
    
    /** @var string Expression to create primary key */
    public $primary_key_expr = 'integer primary key auto_increment';
    
    /** @var array Datatypes to decode driver specific type and len of field
     * array is based on https://github.com/ikkez/f3-schema-builder/blob/master/lib/db/sql/schema.php
     * trasformed with https://gist.github.com/abbadon1334/cd5394ccc8bf0b411c7d75a60215578e
     */
    public $driverDataTypeTranscodes
        = [
            'BOOLEAN'    => ['type' => 'tinyint', 'len' => 1],
            'INT4'       => ['type' => 'int', 'len' => 11],
            'FLOAT'      => ['type' => 'float'],
            'DOUBLE'     => ['type' => 'decimal', 'len' => '18,6'],
            'VARCHAR256' => ['type' => 'varchar', 'len' => 255],
            'TEXT'       => ['type' => 'text'],
            'TIME'       => ['type' => 'time'],
            'DATE'       => ['type' => 'date'],
            'DATETIME'   => ['type' => 'datetime'],
            'TIMESTAMP'  => ['type' => 'timestamp'],
            'BLOB'       => ['type' => 'blob'],
        ];
    
    /**
     * Field, table and alias name escaping symbol.
     * By SQL Standard it's double quote, but MySQL uses backtick.
     *
     * @var string
     */
    protected $escape_char = '`';
    
    /**
     * Return database table descriptions.
     * DB engine specific.
     *
     * @param string $table
     *
     * @return array
     */
    public function describeTable($table)
    {
        if (!$this->connection->expr('show tables like []', [$table])->get()) {
            return []; // no such table
        }
        
        $result = [];
        
        foreach ($this->connection->expr('describe {}', [$table]) as $row) {
            $row2         = [];
            $row2['name'] = $row['Field'];
            $row2['pk']   = $row['Key'] == 'PRI';
            $row2['type'] = preg_replace('/\(.*/', '', $row['Type']);
            
            $result[] = $row2;
        }
        
        return $result;
    }
}
