<?php

namespace Medoo\Drivers;

use ArrayAccess;
use InvalidArgumentException;
use PDO;
use PDOException;

class DriverOption implements ArrayAccess
{
    public array $options;

    public function __construct(array $options)
    {
        if (isset($options['prefix'])) {
            $this->options['prefix'] = $options['prefix'];
        }

        if (isset($options['testMode']) && $options['testMode'] == true) {
            $this->options['testMode'] = true;
            return;
        }

        $options['type'] = $options['type'] ?? $options['database_type'];

        if (!isset($options['pdo'])) {
            $options['database'] = $options['database'] ?? $options['database_name'];

            if (!isset($options['socket'])) {
                $options['host'] = $options['host'] ?? $options['server'] ?? false;
            }
        }

        if (isset($options['type'])) {
            $this->options['type'] = strtolower($options['type']);

            if ($this->options['type'] === 'mariadb') {
                $this->options['type'] = 'mysql';
            }
        }

        if (isset($options['logging']) && is_bool($options['logging'])) {
            $this->options['logging'] = $options['logging'];
        }

        $option = $options['option'] ?? [];
        $commands = [];

        switch ($this->options['type'] ?? null) {

            case 'mysql':
                // Make MySQL using standard quoted identifier.
                $commands[] = 'SET SQL_MODE=ANSI_QUOTES';

                break;

            case 'mssql':
                // Keep MSSQL QUOTED_IDENTIFIER is ON for standard quoting.
                $commands[] = 'SET QUOTED_IDENTIFIER ON';

                // Make ANSI_NULLS is ON for NULL value.
                $commands[] = 'SET ANSI_NULLS ON';

                break;
        }

        if (isset($options['pdo'])) {
            if (!$options['pdo'] instanceof PDO) {
                throw new InvalidArgumentException('Invalid PDO object supplied.');
            }

            $this->options['pdo'] = $options['pdo'];

            foreach ($commands as $value) {
                $this->options['pdo']->exec($value);
            }

            return;
        }

        if (isset($options['dsn'])) {
            if (is_array($options['dsn']) && isset($options['dsn']['driver'])) {
                $attr = $options['dsn'];
            } else {
                throw new InvalidArgumentException('Invalid DSN option supplied.');
            }
        } else {
            if (
                isset($options['port']) &&
                is_int($options['port'] * 1)
            ) {
                $port = $options['port'];
            }

            $isPort = isset($port);

            switch ($this->options['type']) {

                case 'mysql':
                    $attr = [
                        'driver' => 'mysql',
                        'dbname' => $options['database']
                    ];

                    if (isset($options['socket'])) {
                        $attr['unix_socket'] = $options['socket'];
                    } else {
                        $attr['host'] = $options['host'];

                        if ($isPort) {
                            $attr['port'] = $port;
                        }
                    }

                    break;

                case 'pgsql':
                    $attr = [
                        'driver' => 'pgsql',
                        'host' => $options['host'],
                        'dbname' => $options['database']
                    ];

                    if ($isPort) {
                        $attr['port'] = $port;
                    }

                    break;

                case 'sybase':
                    $attr = [
                        'driver' => 'dblib',
                        'host' => $options['host'],
                        'dbname' => $options['database']
                    ];

                    if ($isPort) {
                        $attr['port'] = $port;
                    }

                    break;

                case 'oracle':
                    $attr = [
                        'driver' => 'oci',
                        'dbname' => $options['host'] ?
                            '//' . $options['host'] . ($isPort ? ':' . $port : ':1521') . '/' . $options['database'] :
                            $options['database']
                    ];

                    if (isset($options['charset'])) {
                        $attr['charset'] = $options['charset'];
                    }

                    break;

                case 'mssql':
                    if (isset($options['driver']) && $options['driver'] === 'dblib') {
                        $attr = [
                            'driver' => 'dblib',
                            'host' => $options['host'] . ($isPort ? ':' . $port : ''),
                            'dbname' => $options['database']
                        ];

                        if (isset($options['appname'])) {
                            $attr['appname'] = $options['appname'];
                        }

                        if (isset($options['charset'])) {
                            $attr['charset'] = $options['charset'];
                        }
                    } else {
                        $attr = [
                            'driver' => 'sqlsrv',
                            'Server' => $options['host'] . ($isPort ? ',' . $port : ''),
                            'Database' => $options['database']
                        ];

                        if (isset($options['appname'])) {
                            $attr['APP'] = $options['appname'];
                        }

                        $config = [
                            'ApplicationIntent',
                            'AttachDBFileName',
                            'Authentication',
                            'ColumnEncryption',
                            'ConnectionPooling',
                            'Encrypt',
                            'Failover_Partner',
                            'KeyStoreAuthentication',
                            'KeyStorePrincipalId',
                            'KeyStoreSecret',
                            'LoginTimeout',
                            'MultipleActiveResultSets',
                            'MultiSubnetFailover',
                            'Scrollable',
                            'TraceFile',
                            'TraceOn',
                            'TransactionIsolation',
                            'TransparentNetworkIPResolution',
                            'TrustServerCertificate',
                            'WSID',
                        ];

                        foreach ($config as $value) {
                            $keyname = strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'], '$1_$2', $value));

                            if (isset($options[$keyname])) {
                                $attr[$value] = $options[$keyname];
                            }
                        }
                    }

                    break;

                case 'sqlite':
                    $attr = [
                        'driver' => 'sqlite',
                        $options['database']
                    ];

                    break;
            }
        }

        if (!isset($attr)) {
            throw new InvalidArgumentException('Incorrect connection options.');
        }

        $driver = $attr['driver'];

        if (!in_array($driver, PDO::getAvailableDrivers())) {
            throw new InvalidArgumentException("Unsupported PDO driver: {$driver}.");
        }

        unset($attr['driver']);

        $stack = [];

        foreach ($attr as $key => $value) {
            $stack[] = is_int($key) ? $value : $key . '=' . $value;
        }

        $dsn = $driver . ':' . implode(';', $stack);

        if (
            in_array($this->options['type'], ['mysql', 'pgsql', 'sybase', 'mssql']) &&
            isset($options['charset'])
        ) {
            $commands[] = "SET NAMES '{$options['charset']}'" . (
                $this->options['type'] === 'mysql' && isset($options['collation']) ?
                    " COLLATE '{$options['collation']}'" : ''
                );
        }

        $this->options['dsn'] = $dsn;

        try {
            $this->options['pdo'] = new PDO(
                $dsn,
                $options['username'] ?? null,
                $options['password'] ?? null,
                $option
            );

            if (isset($options['error'])) {
                $this->options['pdo']->setAttribute(
                    PDO::ATTR_ERRMODE,
                    in_array($options['error'], [
                        PDO::ERRMODE_SILENT,
                        PDO::ERRMODE_WARNING,
                        PDO::ERRMODE_EXCEPTION
                    ]) ?
                        $options['error'] :
                        PDO::ERRMODE_SILENT
                );
            }

            if (isset($options['command']) && is_array($options['command'])) {
                $commands = array_merge($commands, $options['command']);
            }

            foreach ($commands as $value) {
                $this->options['pdo']->exec($value);
            }
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }

    public function offsetExists($offset): bool
    {
        return isset($this->options[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        return $this->options[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->options[] = $value;
        } else {
            $this->options[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        unset($this->options[$offset]);
    }
}