# yii2-transpay
Componente del framework Yii2 para la integración con la API de Transpay

Install
Install via Composer:

composer require jmsa-developer/yii2-transpay
or add

"jmsa-developer/yii2-transpay" : "*"
to the require section of your composer.json file.

Configuring


Global Component

use jmsadeveloper\components\Transpay;

// ...
'components' => [
    // setup component
    'Transpay' => [
        'class' => Transpay::classname(),
        'environment' => Transpay::ENVIRONMENT_DEMO,
        'token_demo' => 'token_demo_example',
    ]
]

or

'components' => [
    // setup component
    'Transpay' => [
        'class' => Transpay::classname(),
        'environment' => Transpay::ENVIRONMENT_PROD,
        'token_prod' => 'token_prod_example',
    ]
]


Once you have setup the component, you can refer it across your application easily:

$transpay = Yii::$app->transpay;
$countries = $transpay->getCountries();