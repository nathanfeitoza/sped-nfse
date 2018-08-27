<?php
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 'On');
require_once '../../bootstrap.php';

use NFePHP\NFSe\NFSe;
use NFePHP\Common\Certificate;
use NFePHP\Common\Soap\SoapCurl;

$arr = [
    "atualizacao" => "2016-08-03 18:01:21",
    "tpAmb" => 2,
    "versao" => 1,
    "razaosocial" => "SUA RAZAO SOCIAL LTDA",
    "cnpj" => "999999999999",
    "cpf" => "",
    "im" => "99999999",
    "cmun" => "4305108", //CAXIAS DO SUL
    "siglaUF" => "RS",
    "pathNFSeFiles" => "/dados/nfse",
    "proxyConf" => [
        "proxyIp" => "",
        "proxyPort" => "",
        "proxyUser" => "",
        "proxyPass" => ""
    ]
];
$configJson = json_encode($arr);
$contentpfx = file_get_contents('../../certs/certificado.pfx');

try {
    //com os dados do config e do certificado já obtidos e desconvertidos
    //a sua forma original e só passa-los para a classe 
    $nfse = new NFSe($configJson, Certificate::readPfx($contentpfx, '123456'));
    //Por ora apenas o SoapCurl funciona com IssNet
    $nfse->tools->loadSoapClass(new SoapCurl());
    //caso o mode debug seja ativado serão salvos em arquivos 
    //a requisicção SOAP e a resposta do webservice na pasta de 
    //arquivos temporarios do SO em sub pasta denominada "soap"
    $nfse->tools->setDebugSoapMode(true);

    $content = $nfse->tools->pedidoNFSePDF(101, 102);
    //error_log(print_r($content, TRUE) . PHP_EOL, 3, '/var/www/tests/sped-nfse/nota.xml');
    $response = $nfse->response->readReturn('return', $content);

    $data = base64_decode($response->resPedidoLoteNFSePDF->{'NFS-ePDF'});
    header('Content-Type: application/pdf');
    echo $data;

} catch (\NFePHP\Common\Exception\SoapException $e) {
    echo $e->getMessage();
} catch (NFePHP\Common\Exception\CertificateException $e) {
    echo $e->getMessage();
} catch (Exception $e) {
    echo $e->getMessage();
}