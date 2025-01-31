<?php
/**
 * ObjectSerializer
 */

namespace UniPayment\Client;

use Exception;

/**
 * ObjectSerializer Class
 */
class ObjectSerializer
{
    /**
     * Serialize data
     * @param  mixed  $data          the data to serialize
     * @param  string|null  $format  the format of the field type of the data
     * @return string|array|object serialized form of $data
     */
    public static function sanitizeForSerialization($data, string $format = null)
    {
        if (is_scalar($data) || null === $data) {
            return $data;
        } elseif ($data instanceof \DateTime) {
            return ($format === 'date') ? $data->format('Y-m-d') : $data->format(\DateTime::ATOM);
        } elseif (is_array($data)) {
            foreach ($data as $property => $value) {
                $data[$property] = self::sanitizeForSerialization($value);
            }

            return $data;
        } elseif (is_object($data)) {
            $values  = [];
            $formats = $data::fieldFormats();
            foreach ($data::fieldTypes() as $property => $fieldType) {
                $getter = $data::getters()[$property];
                $value  = $data->$getter();
                if ($value !== null && !in_array($fieldType, [
                        'DateTime',
                        'bool',
                        'boolean',
                        'byte',
                        'double',
                        'float',
                        'int',
                        'integer',
                        'mixed',
                        'number',
                        'object',
                        'string',
                        'void',
                    ], true) && method_exists($fieldType, 'getAllowableEnumValues') && !in_array($value, $fieldType::getAllowableEnumValues())) {
                    $imploded = implode("', '", $fieldType::getAllowableEnumValues());
                    throw new \InvalidArgumentException("Invalid value for enum '$fieldType', must be one of: '$imploded'");
                }
                if ($value !== null) {
                    $values[$data::attributeMap()[$property]] = self::sanitizeForSerialization($value, $fieldType, $formats[$property]);
                }
            }

            return (object)$values;
        } else {
            return (string)$data;
        }
    }

    /**
     * Sanitize filename by removing path.
     * e.g. ../../sun.gif becomes sun.gif
     * @param  string  $filename  filename to be sanitized
     * @return string the sanitized filename
     */
    public static function sanitizeFilename($filename)
    {
        if (preg_match("/.*[\/\\\\](.*)$/", $filename, $match)) {
            return $match[1];
        } else {
            return $filename;
        }
    }

    /**
     * Take value and turn it into a string suitable for inclusion in
     * the path, by url-encoding.
     * @param  string  $value  a string which will be part of the path
     * @return string the serialized object
     */
    public static function toPathValue($value)
    {
        return rawurlencode(self::toString($value));
    }

    /**
     * Take value and turn it into a string suitable for inclusion in
     * the query, by imploding comma-separated if it's an object.
     * If it's a string, pass through unchanged. It will be url-encoded
     * later.
     * @param  string[]|string|\DateTime  $object  an object to be serialized to a string
     * @param  string|null  $format                the format of the parameter
     * @return string the serialized object
     */
    public static function toQueryValue($object, string $format = null)
    {
        if (is_array($object)) {
            return implode(',', $object);
        } else {
            return self::toString($object, $format);
        }
    }

    /**
     * Take value and turn it into a string suitable for inclusion in
     * the header. If it's a string, pass through unchanged
     * If it's a datetime object, format it in RFC3339
     * @param  string  $value  a string which will be part of the header
     * @return string the header string
     */
    public static function toHeaderValue(string $value)
    {
        return self::toString($value);
    }

    /**
     * Take value and turn it into a string suitable for inclusion in
     * the http body (form parameter). If it's a string, pass through unchanged
     * If it's a datetime object, format it in RFC3339
     * @param  string|\SplFileObject  $value  the value of the form parameter
     * @return string the form string
     */
    public static function toFormValue($value)
    {
        if ($value instanceof \SplFileObject) {
            return $value->getRealPath();
        } else {
            return self::toString($value);
        }
    }

    /**
     * Take value and turn it into a string suitable for inclusion in
     * the parameter. If it's a string, pass through unchanged
     * If it's a datetime object, format it in RFC3339
     * If it's a date, format it in Y-m-d
     * @param  string|\DateTime  $value  the value of the parameter
     * @param  string|null  $format      the format of the parameter
     * @return string the header string
     */
    public static function toString($value, string $format = null)
    {
        if ($value instanceof \DateTime) {
            return ($format === 'date') ? $value->format('Y-m-d') : $value->format(\DateTime::ATOM);
        } else {
            return $value;
        }
    }

    /**
     * Serialize an array to a string.
     * @param  array  $collection                 collection to serialize to a string
     * @param  string  $collectionFormat          the format use for serialization (csv,
     *                                            ssv, tsv, pipes, multi)
     * @param  bool  $allowCollectionFormatMulti  allow collection format to be a multidimensional array
     * @return string
     */
    public static function serializeCollection(array $collection, string $collectionFormat, bool $allowCollectionFormatMulti = false): string
    {
        if ($allowCollectionFormatMulti && ('multi' === $collectionFormat)) {
            // http_build_query() almost does the job for us. We just
            // need to fix the result of multidimensional arrays.
            return preg_replace('/%5B[0-9]+%5D=/', '=', http_build_query($collection, '', '&'));
        }
        switch ($collectionFormat) {
            case 'pipes':
                return implode('|', $collection);

            case 'tsv':
                return implode("\t", $collection);

            case 'ssv':
                return implode(' ', $collection);

            case 'csv':
                // Deliberate fall through. CSV is default format.
            default:
                return implode(',', $collection);
        }
    }

    /**
     * Deserialize a JSON string into an object
     * @param  mixed  $data        object or primitive to be deserialized
     * @param  string  $class      class name is passed as a string
     * @param  null  $httpHeaders  HTTP headers
     * @return object|array|null an single or an array of $class instances
     * @throws Exception
     */
    public static function deserialize($data, $class, $httpHeaders = null)
    {
        if (null === $data) {
            return null;
        } elseif (substr($class, 0, 4) === 'map[') { // for associative array e.g. map[string,int]
            $inner        = substr($class, 4, -1);
            $deserialized = [];
            if (strrpos($inner, ",") !== false) {
                $subClass_array = explode(',', $inner, 2);
                $subClass       = $subClass_array[1];
                foreach ($data as $key => $value) {
                    $deserialized[$key] = self::deserialize($value, $subClass, null);
                }
            }

            return $deserialized;
        } elseif (strcasecmp(substr($class, -2), '[]') === 0) {
            $subClass = substr($class, 0, -2);
            $values   = [];
            foreach ($data as $key => $value) {
                $values[] = self::deserialize($value, $subClass, null);
            }

            return $values;
        } elseif ($class === 'object') {
            settype($data, 'array');

            return $data;
        } elseif ($class === '\DateTime') {
            // Some API's return an invalid, empty string as a
            // date-time property. DateTime::__construct() will return
            // the current time for empty input which is probably not
            // what is meant. The invalid empty string is probably to
            // be interpreted as a missing field/value. Let's handle
            // this graceful.
            if (!empty($data)) {
                return new \DateTime($data);
            } else {
                return null;
            }
        } elseif (in_array($class, [
            'DateTime',
            'bool',
            'boolean',
            'byte',
            'double',
            'float',
            'int',
            'integer',
            'mixed',
            'number',
            'object',
            'string',
            'void',
        ], true)) {
            settype($data, $class);

            return $data;
        } elseif ($class === '\SplFileObject') {
            /** @var \Psr\Http\Message\StreamInterface $data */

            // determine file name
            if (array_key_exists('Content-Disposition', $httpHeaders) && preg_match('/inline; filename=[\'"]?([^\'"\s]+)[\'"]?$/i', $httpHeaders['Content-Disposition'], $match)) {
                $filename = Configuration::getDefaultConfiguration()
                        ->getTempFolderPath().DIRECTORY_SEPARATOR.self::sanitizeFilename($match[1]);
            } else {
                $filename = tempnam(Configuration::getDefaultConfiguration()->getTempFolderPath(), '');
            }

            $file = fopen($filename, 'w');
            while ($chunk = $data->read(200)) {
                fwrite($file, $chunk);
            }
            fclose($file);

            return new \SplFileObject($filename, 'r');
        } elseif (method_exists($class, 'getAllowableEnumValues')) {
            if (!in_array($data, $class::getAllowableEnumValues())) {
                $imploded = implode("', '", $class::getAllowableEnumValues());
                throw new \InvalidArgumentException("Invalid value for enum '$class', must be one of: '$imploded'");
            }

            return $data;
        } else {
            // If a discriminator is defined and points to a valid subclass, use it.
            $discriminator = $class::DISCRIMINATOR;
            if (!empty($discriminator) && isset($data->{$discriminator}) && is_string($data->{$discriminator})) {
                $subclass = '{{invokerPackage}}\Model\\'.$data->{$discriminator};
                if (is_subclass_of($subclass, $class)) {
                    $class = $subclass;
                }
            }
            $instance = new $class();
            foreach ($instance::fieldTypes() as $property => $type) {
                $propertySetter = $instance::setters()[$property];

                if (!isset($propertySetter) || !isset($data->{$instance::attributeMap()[$property]})) {
                    continue;
                }

                $propertyValue = $data->{$instance::attributeMap()[$property]};
                if (isset($propertyValue)) {
                    $instance->$propertySetter(self::deserialize($propertyValue, $type, null));
                }
            }

            return $instance;
        }
    }
}
