<?php

namespace Mini\Cms\Modules\Respositories\Territory;

class AddressFormat
{
    /**
     * @var array|mixed
     */
    private mixed $address_options;

    /**
     *
     */
    public function __construct()
    {
        $this->address_options = [];
        $path = __DIR__ . '/data/addressfield.json';
        if (file_exists($path)) {
            $this->address_options = json_decode(file_get_contents($path), true)['options'] ?? [];
        }
    }

    /**
     * @return array
     */
    public function getAddressOptions(): array
    {
        return $this->address_options;
    }

    /**
     * @param string $country_code
     * @return array
     */
    public  function getAddressOption(string $country_code): array
    {
        $address = array_filter($this->address_options, function ($address) use ($country_code) {
            return $address['iso'] === $country_code;
        });
        $address = reset($address);
        return is_array($address) ? $address : [];
    }

    /**
     * @param string $country_code
     * @return array
     */
    public function getAddressField(string $country_code): array
    {
        $address = $this->getAddressOption($country_code);
        return $address['fields'] ?? [];
    }

    /**
     * @param string $country_code
     * @param string $name_key
     * @return string|null
     */
    public function getAddressFieldsMarkUp(string $country_code, string $name_key): string|null
    {
        $address = $this->getAddressField($country_code);
        $fields = self::fieldNames();
        $mark_up = null;
        $outside_fields = null;
        $inside_fields = null;
        if(!empty($address)) {
            foreach ($fields as $key=>$field) {
                $field_data = $this->isAddressField($address, $key);
                if(!empty($field_data)) {
                    extract(['content' => $field_data]);
                    extract($field);
                    $field_field_main = $name_key;
                    ob_start();
                    require __DIR__. $field['template'];
                    if($key == 'administrativearea' || $key == 'thoroughfare' || $key == 'premise') {
                       $outside_fields .= ob_get_clean();
                    }
                    elseif ($key == 'localityname' || $key == 'postalcode' || $key == 'dependent_localityname') {
                       $inside_fields .= ob_get_clean();
                    }
                    ob_clean();
                    ob_flush();
                }
            }
        }
        return <<<FIELDS
<div class="p-4 border border-1 rounded bg-light">
$outside_fields
<div class="row bordered">
       $inside_fields
    </div>
</div>
FIELDS;
    }

    /**
     * @return array[]
     */
    public static function fieldNames(): array
    {
        return [
            'administrativearea' =>[
                'name' => 'administrative_area',
                'label'=>'State/Province/Region/County',
                'template' => '/templates/administrative_area.php',
                'storage_field_name' => 'state_code',
            ],
            'thoroughfare' =>[
                'name'=> 'address_1',
                'label'=>'Address 1',
                'template' => '/templates/thoroughfare.php',
                'storage_field_name' => 'address_1',
            ],
            'premise' => [
                'name'=> 'address_2',
                'label'=>'Address 2',
                'template' => '/templates/premise.php',
                'storage_field_name' => 'address_2',
            ],
            'localityname' =>[
                'name'=> 'city',
                'label'=>'City/District',
                'template' => '/templates/localityname.php',
                'storage_field_name' => 'city_id',
            ],
            'postalcode' =>[
                'name'=> 'postalcode',
                'label'=>'Postal Code',
                'template' => '/templates/postalcode.php',
                'storage_field_name' => 'zip_code',
            ],
            'dependent_localityname' =>[
                'name'=> 'administrative_area',
                'label'=>'Province/Region/County',
                'template' => '/templates/dependent_localityname.php',
                'storage_field_name' => 'county',
            ]

        ];
    }

    /**
     * @param array $data
     * @param string $key
     * @return mixed
     */
    private function isAddressField(array $data, string $key): mixed
    {
        return self::recursiveSearch($data, $key);
    }

    /**
     * @param array $data
     * @param string $key
     * @return mixed
     */
    private static function recursiveSearch(array $data, string $key): mixed
    {
        $results = [];
        foreach ($data as $k => $v) {
            if ($k === $key) {
                return $v;
            } elseif (is_array($v)) {
                $results = self::recursiveSearch($v, $key);
                if(!empty($results)) {
                    return $results;
                }
            }
        }
        return $results;
    }

    public function getAddressMarkUp(string $key,string $default_country = 'US'): string
    {
        $default_country = empty($default_country) ? 'US' : $default_country;
        $countries = new Countries();
        $countries = $countries->getCountries();
        $fields = $this->getAddressFieldsMarkUp($default_country, $key);
        $main_field = $key;
        ob_start();
        require __DIR__. '/templates/country.php';
        return ob_get_clean();
    }

    public static function addressAsset(): string
    {
        return __DIR__ . '/templates/address_field.js';
    }

    public static function filterAddressValues(array $payload2, string $main_field): array
    {
        $fields = self::fieldNames();
        $address_values = [];
        foreach ($fields as $key=>$field) {
            if(isset($payload2[$main_field . '___' . $key])) {
                $address_values[$key] = $payload2[$main_field . '___' . $key];
            }
        }
        return $address_values;
    }
}