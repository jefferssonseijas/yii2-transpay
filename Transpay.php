<?php
/**
 * Created by PhpStorm.
 * User: JMSA
 * Date: 31-May-19
 * Time: 9:44 AM
 */

namespace jmsadeveloper\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;


class Transpay extends Component
{
    public $environment;

    public $token_demo;
    public $token_prod;

    const URL_DEMO = 'https://demo-api.transfast.net/api/';
    const URL_PROD = 'https://send.transfast.ws/api/';

    private $url;
    private $token;

    const ENVIRONMENT_DEMO = 'DEMO';
    const ENVIRONMENT_PROD = 'PROD';

    const URL_COUNTRIES = 'catalogs/countries';
    const URL_STATES = 'catalogs/states?';
    const URL_CITIES = 'catalogs/cities?';
    const URL_PAY = 'transaction/invoice';
    const URL_BANKS = 'catalogs/banks?';
    const URL_CURRENCIES = 'transaction/receivercurrencies?';
    const URL_BANKS_BRANCH = 'catalogs/BankBranch?';
    const URL_BRANCH_PAYERS = 'catalogs/payers?';
    const URL_CHECK_BALANCE = 'accounting/balanceandcredit?';
    const URL_CHECK_TRANSACTION_STATUS = 'transaction/bytfpin?';
    const URL_OCCUPATIONS = 'catalogs/SenderOccupation';
    const URL_REQUIRED_FIELDS = 'requiredfields/postinvoice';


    public function init()
    {

        if ($this->environment === null) {
            throw new InvalidConfigException('You must define the environment variable');
        }

        if($this->environment === self::ENVIRONMENT_DEMO)
        {

            if ($this->token_demo === null) {
                throw new InvalidConfigException('You must define the token demo variable');
            }

            $this->url = self::URL_DEMO;
            $this->token = $this->token_demo;
        }
        elseif($this->environment === self::ENVIRONMENT_PROD)
        {

            if ($this->token_prod === null) {
                throw new InvalidConfigException('You must define the token prod variable');
            }

            $this->url = self::URL_PROD;
            $this->token = $this->token_prod;
        }else{
            throw new InvalidConfigException('Wrong enviroment, must be one of the constant environment already defined');

        }

    }


    /**
     * Base function that is responsible for interacting directly with the transpay api to obtain data
     * @param $url
     * @param array $params
     * @return array
     */
    public function getData($url, $params = [])
    {
        $this->url = $this->url . $url;

        $this->url .= http_build_query($params);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: Credentials ' . $this->token)
        );

        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        $error = curl_error($curl);

        if ($error) {
            Yii::error($info);
            Yii::error($error);
        }

        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return [
            'status' => $status,
            'response' => json_decode($response, true)
        ];

    }

    /**
     * Base function that is responsible for interacting directly with the transpay api to send data
     * @param $url
     * @param array $dataArray
     * @return array
     */
    public function putData($url, $dataArray)
    {
        $this->url = $this->url . $url;

        $jsonArray = json_encode($dataArray);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POST, count($dataArray));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonArray);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_VERBOSE, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: Credentials ' . $this->token)
        );

        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        $error = curl_error($curl);

        if ($error) {
            Yii::error($info);
            Yii::error($error);
        }

        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return [
            'status' => $status,
            'response' => json_decode($response, true)
        ];
    }

    /**
     * Get the status of a transaction through its tfpin
     * @param $tfpin
     * @return array
     */
    public function getTransactionStatus($tfpin)
    {
        $url = self::URL_CHECK_TRANSACTION_STATUS;

        $params = [
            'TfPin'=>$tfpin
        ];

        return $this->getData($url, $params);

    }

    /**
     * Obtain the balance of an account using the iso code of the currency
     * @param $currencyIsoCode
     * @return array
     */
    public function getBalance($currencyIsoCode)
    {
        $url = self::URL_CHECK_BALANCE;

        $params = [
            'currencyisocode'=>$currencyIsoCode
        ];

        return $this->getData($url, $params);
    }

    /**
     * Get the branches of a bank using the code of the bank and the city
     * @param $bank
     * @param $city
     * @return array
     */
    public function getBanksBranch($bank, $city)
    {
        $url = self::URL_BANKS_BRANCH;

        $params = [
            'BankId'=>$bank,
            'CityID'=>$city
        ];

        return $this->getData($url, $params);

    }

    /**
     * Get the different currencies available in the country, state, city and mode of payment
     * @param $country
     * @param $state
     * @param $city
     * @param string $paymentmode
     * @return array
     */
    public function getCurrencies($country, $state, $city, $paymentmode)
    {
        $url = self::URL_CURRENCIES;

        $params = [
            'countryisocode'=>$country,
            'stateid'=>$state,
            'CityId'=>$city,
            'PaymentModeId'=>$paymentmode
        ];

        return $this->getData($url, $params);

    }

    /**
     * Get the cities for a given country and state
     * @param $country
     * @param $state
     * @return array
     */
    public function getCities($country, $state)
    {
        $url = self::URL_CITIES;

        $params = [
            'countryisocode'=>$country,
            'stateid'=>$state,

        ];

        $response = $this->getData($url, $params);

        return $response;
    }

    /**
     * Get the states for a specific country
     * @param $country
     * @return array
     */
    public function getStates($country)
    {
        $url = self::URL_STATES;

        $params = [
            'countryisocode'=>$country,

        ];

        $response = $this->getData($url, $params);

        return $response;
    }

    /**
     * Get the available countries
     * @return array
     */
    public function getCountries()
    {
        $url = self::URL_COUNTRIES;

        $response = $this->getData($url);

        return $response;
    }

    /**
     * Get the occupations available to select from the sender of the payment
     * @return array
     */
    public function getSenderOccupation()
    {
        $url = self::URL_OCCUPATIONS;

        $response = $this->getData($url);

        return $response;
    }

    /**
     * Get the available banks for a country
     * @param $country
     * @return array
     */
    public function getBanks($country)
    {
        $url = self::URL_BANKS;

        $params = [
            'countryisocode'=>$country,
        ];

        $response = $this->getData($url, $params);

        return $response;
    }

    /**
     * Gets the fields required to send a payment
     * @return array
     */
    public function getRequiredFields()
    {
        $url = self::URL_REQUIRED_FIELDS;

        $response = $this->getData($url);

        if($response['status']=='200')
        {
            return $response;

        }
        return $response;
    }


    /**
     * Gets available branches from payers
     * @param $country
     * @param $state
     * @param $city
     * @param $receivecurrencyisocode
     * @param $bank
     * @param $paymentmode
     * @param $sourcecurrencyisocode
     * @return array
     */
    public function getBranchPayers($country, $state, $city, $receivecurrencyisocode, $bank, $paymentmode, $sourcecurrencyisocode)
    {
        $url = self::URL_BRANCH_PAYERS;

        $params = [
            'countryisocode'=>$country,
            'stateid'=>$state,
            'cityid'=>$city,
            'paymentmodeid'=>$paymentmode,
            'sourcecurrencyisocode'=>$sourcecurrencyisocode,
            'receivecurrencyisocode'=>$receivecurrencyisocode,
            'bank'=>$bank
        ];

        return $this->getData($url, $params);
    }

}
