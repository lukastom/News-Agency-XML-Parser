<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

<?php

/*-------------------------odstavce místo newlines--------------------------------------------------------------------------------*/
function odstavce($string) {

	//$string = str_replace(array("\r\n\n", "\r\r\n", "\r\n", "\r", "\n"), "</p><p>", $string);
	$string = str_replace(array("\n\n"), "</p><p>", $string);
	//$string = "<p>" . $string . "</p>";

	return $string;

}

function odstavce_vycisti($string) {

	$string = str_replace("</p><p>", "", $string);

	return $string;

}

/*-------------------------náhodná IP--------------------------------------------------------------------------------*/
function randomip()
{
	$rand = array();
	for($i = 0; $i < 4; $i++)
		{
		$rand[] = rand(1,255);
		}
	$ip = "$rand[0].$rand[1].$rand[2].$rand[3]";
	return($ip);
}

/*-------------------------funkce kontroluje jestli je číslo násobkem čísla-----------------------------------*/

function je_nasobek ($cislo, $jakym_cislem)
{
	if($cislo%$jakym_cislem == 0) return true;
		else return false;
}

//-------------------------------třída překladače---------------------------------------------------------------------------------------------------
/**
* GTranslate - A class to comunicate with Google Translate(TM) Service
*               Google Translate(TM) API Wrapper
*               More info about Google(TM) service can be found on http://code.google.com/apis/ajaxlanguage/documentation/reference.html
*               This code has o affiliation with Google (TM) , its a PHP Library that allows to comunicate with public a API
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
* @author Jose da Silva <jose@josedasilva.net>
* @since 2009/11/18
* @version 0.7.4
* @licence LGPL v3
*
* <code>
* <?
* require_once("GTranslate.php");
* try{
*       $gt = new Gtranslate;
*       echo $gt->english_to_german("hello world");
* } catch (GTranslateException $ge)
* {
*       echo $ge->getMessage();
* }
* ?>
* </code>
*/


/**
* Exception class for GTranslated Exceptions
*/

class GTranslateException extends Exception
{
	public function __construct($string) {
	parent::__construct($string, 0);
	}

	public function __toString() {
	return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	}
}

class GTranslate
{
	/**
	* Google Translate(TM) Api endpoint
	* @access private
	* @var String
	*/
	private $url = "http://ajax.googleapis.com/ajax/services/language/translate";

	/**
	* Google Translate (TM) Api Version
	* @access private
	* @var String
	*/
	private $api_version = "2.0";

	/**
	* Comunication Transport Method
	* Available: http / curl
	* @access private
	* @var String
	*/
	private $request_type = "http";

	/**
	* Path to available languages file
	* @access private
	* @var String
	*/
	private $available_languages_file       = "languages.ini";

	/**
	* Holder to the parse of the ini file
	* @access private
	* @var Array
	*/
	private $available_languages = array();

	/**
	* Google Translate api key
	* @access private
	* @var string
	*/
	private $api_key = "ABQIAAAAE42F_jOZV5FbbEaycLh-KhT0302ZmBTdgxHz4oO_crQjpRairhTyeFc_4FIMDa5EWLScOSRqhD50kQ";

	/**
	* Google request User IP
	* @access private
	* @var string
	*/
	private $user_ip = randomip;

	/**
	* Constructor sets up {@link $available_languages}
	*/
	public function __construct()
	{
		$this->available_languages = parse_ini_file("/home/domeny/domain.cz/web/subdomeny/www/wp-content/plugins/newsml-parser/languages.ini");
	}

	/**
	* URL Formater to use on request
	* @access private
	* @param array $lang_pair
	* @param array $string
	* "returns String $url
	*/

	private function urlFormat($lang_pair,$string)
	{
		$parameters = array(
		"v" => $this->api_version,
		"q" => $string,
		"langpair"=> implode("|",$lang_pair)
		);

		if(!empty($this->api_key))
		{
			$parameters["key"] = $this->api_key;
		}

		if( empty($this->user_ip) )
		{
			if( !empty($_SERVER["REMOTE_ADDR"]) )
		{
				$parameters["userip"]   =       $_SERVER["REMOTE_ADDR"];
		}
		} else
		{
			$parameters["userip"]   =       $this->user_ip;
		}

		$url  = "";

		foreach($parameters as $k=>$p)
		{
			$url    .=      $k."=".urlencode($p)."&";
		}
		return $url;
	}

	/**
	* Define the request type
	* @access public
	* @param string $request_type
	* return boolean
	*/
	public function setRequestType($request_type = 'http') {
		if (!empty($request_type)) {
			$this->request_type = $request_type;
			return true;
		}
		return false;
		}

	/**
	* Define the Google Translate Api Key
	* @access public
	* @param string $api_key
	* return boolean
	*/
	public function setApiKey($api_key) {
	if (!empty($api_key)) {
		$this->api_key = $api_key;
		return true;
	}
		return false;
	}

	/**
	* Define the User Ip for the query
	* @access public
	* @param string $ip
	* return boolean
	*/
	public function setUserIp($ip) {
		if (!empty($ip)) {
		$this->user_ip = $ip;
		return true;
	}
		return false;
	}

	/**
	* Query the Google(TM) endpoint
	* @access private
	* @param array $lang_pair
	* @param array $string
	* returns String $response
	*/

	public function query($lang_pair,$string)
	{
		$query_url = $this->urlFormat($lang_pair,$string);
		$response = $this->{"request".ucwords($this->request_type)}($query_url);
		return $response;
	}

	/**
	* Query Wrapper for Http Transport
	* @access private
	* @param String $url
	* returns String $response
	*/

	private function requestHttp($url)
	{
		return GTranslate::evalResponse(json_decode(file_get_contents($this->url."?".$url)));
	}

	/**
	* Query Wrapper for Curl Transport
	* @access private
	* @param String $url
	* returns String $response
	*/

	private function requestCurl($url)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_REFERER, !empty($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $url);
		$body = curl_exec($ch);
		curl_close($ch);
		return GTranslate::evalResponse(json_decode($body));
	}

	/**
	* Response Evaluator, validates the response
	* Throws an exception on error
	* @access private
	* @param String $json_response
	* returns String $response
	*/

	private function evalResponse($json_response)
	{
		switch($json_response->responseStatus)
	{
		case 200:
		return $json_response->responseData->translatedText;
		break;
		//case 400 a 404: přidáno pro případy "invalid result" - nicméně daný článek se přeskočí a nepřeloží
		case 400:
		return $json_response->responseData->translatedText;
		break;
		case 404:
		return $json_response->responseData->translatedText;
		break;
		default:
		throw new GTranslateException("Unable to perform Translation:".$json_response->responseDetails);
		break;
	}
	}


	/**
	* Validates if the language pair is valid
	* Throws an exception on error
	* @access private
	* @param Array $languages
	* returns Array $response Array with formated languages pair
	*/

	private function isValidLanguage($languages)
	{
		$language_list  = $this->available_languages;

		$languages              =       array_map( "strtolower", $languages );
		$language_list_v        =       array_map( "strtolower", array_values($language_list) );
		$language_list_k        =       array_map( "strtolower", array_keys($language_list) );
		$valid_languages        =       false;
		if( TRUE == in_array($languages[0],$language_list_v) AND TRUE == in_array($languages[1],$language_list_v) )
	{
		$valid_languages        =       true;
	}

	if( FALSE === $valid_languages AND TRUE == in_array($languages[0],$language_list_k) AND TRUE == in_array($languages[1],$language_list_k) )
	{
		$languages      =       array($language_list[strtoupper($languages[0])],$language_list[strtoupper($languages[1])]);
		$valid_languages        =       true;
	}

	if( FALSE === $valid_languages )
	{
		throw new GTranslateException("Unsupported languages (".$languages[0].",".$languages[1].")");
	}

		return $languages;
	}

	/**
	* Magic method to understande translation comman
	* Evaluates methods like language_to_language
	* @access public
	* @param String $name
	* @param Array $args
	* returns String $response Translated Text
	*/


	public function __call($name,$args)
	{
		$languages_list         =       explode("_to_",strtolower($name));
		$languages = $this->isValidLanguage($languages_list);

		$string         =       $args[0];
		return $this->query($languages,$string);
	}
	}

//-------------------------------ořezávátko (počet znaků, zachová slova, a HTML------------------------------------------------------------------------

function orizni($string, $limit, $break=" ") {

	//tato funkce ořízne string na maximální počet znaků (včetně tagů), zachová celá slova a celé HTML tagy (to co je mezi < a >)

	// return with no change if string is shorter than $limit
	if(strlen($string) <= $limit) return $string;
	$string = substr($string, 0, $limit);
	if(false !== ($breakpoint = strrpos($string, $break))) {
		$string = substr($string, 0, $breakpoint);
	}

	//v případě neukončeného tagu - oriznuti vseho od posledniho vyskytu <
	if (strrpos($string,"<")>strrpos($string,">")) {
		$string = preg_replace('/(<)[^<]*$/', '', $string);
	}

	return $string;
}

//-------------------------------funkce překladače------------------------------------------------------------------------------------------------------

function preloz($text) {

	$zpusob_pripojeni = "CURL";

	$max_delka = 5000;

	if (strlen($text)<$max_delka) {
		//$vysledny_text = translate_max_500 ($text);

		$gt = new Gtranslate;
		if ($zpusob_pripojeni == "HTTP"){
			$vysledny_text = $gt->slovak_to_czech($text);
		} elseif ($zpusob_pripojeni == "CURL"){
			$gt->setRequestType('curl');
			$vysledny_text = $gt->slovak_to_czech($text);
		}

	} else {

	for ($pocitadlo_orez = 0; strlen($text)>$max_delka; ($pocitadlo_orez = $pocitadlo_orez+1)) {

		$cast_textu[$pocitadlo_orez] = orizni($text, $max_delka);

		$text = str_replace($cast_textu[$pocitadlo_orez], "", $text);
		if (strlen($text)<$max_delka) {
		$cast_textu[($pocitadlo_orez+1)] = $text;
		}
	}

	//echo "kontrola funkce";
	//die;

	$pocet_kusu_textu = count($cast_textu);

	for ($pocitadlo_prekl = 0; $pocitadlo_prekl<$pocet_kusu_textu; ($pocitadlo_prekl = $pocitadlo_prekl+1)) {
		//$cast_textu[$pocitadlo_prekl] = translate_max_500 ($cast_textu[$pocitadlo_prekl]);

		$gt = new Gtranslate;
		if ($zpusob_pripojeni == "HTTP"){
			$cast_textu[$pocitadlo_prekl] = $gt->slovak_to_czech($cast_textu[$pocitadlo_prekl]);
		} elseif ($zpusob_pripojeni == "CURL"){
			$gt->setRequestType('curl');
			$cast_textu[$pocitadlo_prekl] = $gt->slovak_to_czech($cast_textu[$pocitadlo_prekl]);
		}

	}

	for ($pocitadlo_dohr = 0; $pocitadlo_dohr<$pocet_kusu_textu; ($pocitadlo_dohr = $pocitadlo_dohr+1)) {
		$vysledny_text = $vysledny_text . $cast_textu[$pocitadlo_dohr];
	}
	}

	return $vysledny_text;
}

function oriznout_na_pocet_slov($string, $count){
	$words = explode(' ', $string);
	if (count($words) > $count){
		array_splice($words, $count);
		$string = implode(' ', $words);
	}
	return $string;
}

/*--otevření databáze, načtení titlu posledního vydaného článku, je v $posledni_title --*/

// Připojení k databázi.
$db_spojeni = mysqli_connect
('localhost', '[jmeno]', '[heslo]', 'domain_sita_title', 3306);

// Otestování, zda se připojení podařilo.
if ($db_spojeni)
	echo 'Připojení se podařilo<br /><br />';
else
{
	echo 'Připojení se nepodařilo, sorry';
	echo '<br />';
	echo 'Popis chyby: ', mysqli_connect_error();
	exit();
}

// Zaslání SQL příkazu do databáze.
$objekt_vysledku = mysqli_query($db_spojeni, 'SELECT * FROM poslednivydany');

if (!$objekt_vysledku)
{
	echo 'Poslání SQL příkazu se nepodařilo, sorry';
	echo '<br />';
	echo 'Popis chyby: ', mysqli_error($db_spojeni);
	exit();
}

// Zobrazení titlu (jde o title posledního vydaného článku).
$radek = mysqli_fetch_array($objekt_vysledku);
$posledni_title = $radek['title'];
//echo '<b>Title posledního vydaného článku: ', $posledni_title, '</b><br />';
//echo '<br />';

/*----------------------otevření XML feedu, uložení do proměnné--------------------------*/

//$url = "http://www.webnoviny.sk/xml/NewsML/UTF-8/klient123/456";
$url = "http://www.webnoviny.sk/xml/NewsML/UTF-8/klient123/456";
$xml = new SimpleXMLElement(file_get_contents($url));

/*-------------------------includování XML-RPC klienta-----------------------------------*/

include ('../../../wp-includes/class-IXR.php');
$client = new IXR_Client('http://www.domain.cz/xmlrpc.php');

/*---------------------------parsovací cyklus--------------------------------------------*/

/* počítadlo */
$pocitadlo_cyklu = 0;

/* NewsItem je větev úroveň 1, obaluje příspěvek */
foreach($xml->NewsItem as $sprava){

/* počítadlo, zvětšování o 1, výpis */
$pocitadlo_cyklu = $pocitadlo_cyklu+1;
//echo 'Počítadlo cyklu: <b>', $pocitadlo_cyklu, '</b><br />';

/* tvorba článku z elementů z XML */

/* element NewsComponent->DescriptiveMetadata->SubjectCode->Subject->attributes()->FormalName - druh zprávy*/
$druh_zpravy_pom=$sprava->NewsComponent->DescriptiveMetadata->SubjectCode->Subject->attributes()->FormalName;
$druh_zpravy = "$druh_zpravy_pom";

/* element NewsComponent->NewsLines->HeadLine - titulek */
/* title určuje, co bude v titulku článku */
/* udělal se převod na string */
$content['title']=$sprava->NewsComponent->NewsLines->HeadLine;
$title_pom = $content['title'];
$content['title']="$title_pom";
//      echo $content['title'];
//      echo '<br>';

$puvodni_retezec = $content['title'];
$toto_nechci = "&amp;";
$nahradit_timto = "&";
$content['title']=str_replace($toto_nechci, $nahradit_timto, $puvodni_retezec);

$puvodni_retezec = $content['title'];
$toto_nechci = "&quot;";
$nahradit_timto = "\"";
$content['title']=str_replace($toto_nechci, $nahradit_timto, $puvodni_retezec);

/* element NewsComponent->NewsComponent->ContentItem->DataContent - obsah zprávy */
/* description určuje, co bude v těle, obsahu článku */
$content['description']=$sprava->NewsComponent->NewsComponent->ContentItem->DataContent;

$description_pom = $content['description'];
$content['description']="$description_pom";

/* &amp; nahradit ampersandem a &quot; nahradit uvozovkami*/
$puvodni_retezec = $content['description'];
$toto_nechci = "&amp;";
$nahradit_timto = "&";
$content['description']=str_replace($toto_nechci, $nahradit_timto, $puvodni_retezec);

$puvodni_retezec = $content['description'];
$toto_nechci = "&quot;";
$nahradit_timto = "\"";
$content['description']=str_replace($toto_nechci, $nahradit_timto, $puvodni_retezec);

/* je to PR článek? */
if(ereg("WBN\/PR", $content['description'])) {
	/* vyříznutí všeho mezi BRATISLAVA ... až po - */
	$content['description'] = preg_replace("/(BRATISLAVA)(.*)(WBN\/PR\) - )/s", "", $content['description']);
	$je_to_pr_clanek = true;
} else if (ereg("WORKSHOP", $content['description'])) {
	$je_to_pr_clanek = true;

	/* není to PR článek? */
} else {
	/* vyříznutí všeho mezi Foto nebo Ilustračné nebo ... až po - */
	//       $content['description'] = preg_replace("/(Ilustračné|Zsolt|Bez|logo|Zľava|Ewald|Pavol|Martin|
	//       Robert|Erkki|Michel|Luboš|Ľubomír|Klaus|Olli|Ivan|Iveta|Miloš|Viera|Angela|Ján|Brian|José|Nova|
	//       Jacek|Jozef|Dominique|Didier|Milan|Ing|Daniel|Jerome|Elena|George|Jeff|Wen|Charles|Minister|George|
	//       Dôvera|ilustračná|Ilustračná|Miroslav|ľava|Poslanec|Roman|András|Premiérka|Eduard|Axel|Eva|Grécko|
	//      Japonský|Ben|Burza|Jean-​Claude|David|Branislav|George|Igor|BUDAPEŠŤ|Čulenova|Vladimír|Juraj|Michal|
	//       Ladislav|Jose|Komerční|Jean|Jaroslav|Paul|Iustračné|Danišovce|Wolfgang|Maďarský|Árpád|Johan|Barack|
	//       Gyorgy|Oleg|Japonská|Nicolas|Dušan|Timothy|Lucia|Český|Prezident|Generálny|Grécky|Dick|Dublin|Eamon|
	//       James|Dacian|Ajai|Emil|Elektromobil|Írsky|Teixeira|Petr|Antik|Patrick|Herman|Donald|Guido|Michal|Európsky|
	//       Angel|Zasadnutie|Amadeu|Nouriel|Predseda|Otvorenie|Košice|Vladimir|Registračná|Mesto|Španielsky|Rainer|Riaditeľ|
	//       Predseda|Nemecká|Róbert|Jana|František|Šéf|Richard|Palubná|Karol|Kafiléria|Výrobné|László|Ropné|Česká|Čistička|
	//       Trnava|Viktor|Christine|Philippe|Daňové|Peter|Ole|ILustračné|Dortmund|Alexander|Varšava|Trenčianske|Costis|
	//       summit|Frank-Walter|Predsedníčka|Ľudovít|Galanta|Ilutračné|Marek|Pohľad|Metropolis|Daňový|Európska|Bank|
	//       Meander|Saxo|PEKING|LONDÝN|Kyjev|Slovenská)(.*)(WEBNOVINY\) - |MEDIAFAX\) - |WEBNOVINY\)|Reuters\) - |WEBNVOINY\) - |WEBNNOVINY\) - )/s", "", $content['description']);

	$content['description'] = preg_replace("/^(.*)(WEBNOVINY\) - |MEDIAFAX\) - |WEBNOVINY\)|Reuters\) - |WEBNVOINY\) - |WEBNNOVINY\) - )/s", "", $content['description']);


	/* vyříznutí všeho mezi Foto nebo Ilustračné nebo ... až po - */
	$content['description'] = preg_replace("/(doFoto)(.*)(WEBNOVINY\) - )/s", "", $content['description']);

	/* vyříznutí všeho mezi Foto nebo Ilustračné nebo ... až po - */
	$content['description'] = preg_replace("/(Foto)(.*)(WEBNOVINY\) - )/s", "", $content['description']);

	/* vyříznutí všeho mezi Foto nebo Ilustračné nebo ... až po - */
	$content['description'] = preg_replace("/(BRATISLAVA)(.*)(WEBNOVINY\) - )/s", "", $content['description']);

	$je_to_pr_clanek = false;
}

$puvodni_retezec = $content['description'];
$toto_nechci = 'Foto:';
$nahradit_timto = "";
$content['description']=str_replace($toto_nechci, $nahradit_timto, $puvodni_retezec);

$puvodni_retezec = $content['description'];
$toto_nechci = '<html xmlns="http://www1.webnoviny.sk/Newsml/xhtml.xsd">';
$nahradit_timto = "";
$content['description']=str_replace($toto_nechci, $nahradit_timto, $puvodni_retezec);

$puvodni_retezec = $content['description'];
$toto_nechci = '</head>';
$nahradit_timto = "";
$content['description']=str_replace($toto_nechci, $nahradit_timto, $puvodni_retezec);

$puvodni_retezec = $content['description'];
$toto_nechci = '<head>';
$nahradit_timto = "";
$content['description']=str_replace($toto_nechci, $nahradit_timto, $puvodni_retezec);

$puvodni_retezec = $content['description'];
$toto_nechci = '<title/>';
$nahradit_timto = "";
$content['description']=str_replace($toto_nechci, $nahradit_timto, $puvodni_retezec);

$puvodni_retezec = $content['description'];
$toto_nechci = '</body>';
$nahradit_timto = "";
$content['description']=str_replace($toto_nechci, $nahradit_timto, $puvodni_retezec);

$puvodni_retezec = $content['description'];
$toto_nechci = '<body>';
$nahradit_timto = "";
$content['description']=str_replace($toto_nechci, $nahradit_timto, $puvodni_retezec);

$puvodni_retezec = $content['description'];
$toto_nechci = '</html>';
$nahradit_timto = "";
$content['description']=str_replace($toto_nechci, $nahradit_timto, $puvodni_retezec);

$puvodni_retezec = $content['description'];
$toto_nechci = 'SITA, Reuters';
$nahradit_timto = "";
$content['description']=str_replace($toto_nechci, $nahradit_timto, $puvodni_retezec);

$puvodni_retezec = $content['description'];
$toto_nechci = 'SITA, Mediafax';
$nahradit_timto = "";
$content['description']=str_replace($toto_nechci, $nahradit_timto, $puvodni_retezec);

$puvodni_retezec = $content['description'];
$toto_nechci = 'SITA, ČIA';
$nahradit_timto = "";
$content['description']=str_replace($toto_nechci, $nahradit_timto, $puvodni_retezec);

$puvodni_retezec = $content['description'];
$toto_nechci = 'SITA';
$nahradit_timto = "";
$content['description']=str_replace($toto_nechci, $nahradit_timto, $puvodni_retezec);

$puvodni_retezec = $content['description'];
$toto_nechci = '/Tomáš';
$nahradit_timto = "";
$content['description']=str_replace($toto_nechci, $nahradit_timto, $puvodni_retezec);

$puvodni_retezec = $content['description'];
$toto_nechci = "/AP";
$nahradit_timto = "";
$content['description']=str_replace($toto_nechci, $nahradit_timto, $puvodni_retezec);

$puvodni_retezec = $content['description'];
$toto_nechci = "tripadvisor.com";
$nahradit_timto = "";
$content['description']=str_replace($toto_nechci, $nahradit_timto, $puvodni_retezec);

$puvodni_retezec = $content['description'];
$toto_nechci = "  /";
$nahradit_timto = "";
$content['description']=str_replace($toto_nechci, $nahradit_timto, $puvodni_retezec);

$puvodni_retezec = $content['description'];
$toto_nechci = "  – ";
$nahradit_timto = "";
$content['description']=str_replace($toto_nechci, $nahradit_timto, $puvodni_retezec);

//      echo $content['description'];
//      echo '<br>';

/* element NewsComponent->NewsComponent->ContentItem - obrázek */
/* v XML jsou 2 NewsComponent->NewsComponent, běháme po nich a hledáme ten, který má attribut Essential="no", pak vytáhneme atribut Href */
/* image určuje, co bude obrázek */
$xml_element=$sprava->NewsComponent;
foreach($xml_element->NewsComponent as $NewsComponent){
	if($NewsComponent['Essential'] == 'no') {
		//         echo '<img src="';
		$content['image'] = $NewsComponent->ContentItem->attributes()->Href;
		$image_pom = $content['image'];
		$content['image']="$image_pom";
		//         echo $content['image'];
		//         echo '" alt="Pokus 2" /><br>';
	}
}

/* element NewsManagement->ThisRevisionCreated - datum publikování */
/* dateCreated určuje datum vydání */
/* nějak to nefunguje, ale nevím proč */
$datum_vydani=$sprava->NewsManagement->ThisRevisionCreated;
//      echo $datum_vydani;
//      echo '<br>';

/* element NewsComponent->DescriptiveMetadata->SubjectCode->SubjectMatter->attributes()->FormalName - kategorie */
$kategorie_zpravy_pom=$sprava->NewsComponent->DescriptiveMetadata->SubjectCode->SubjectMatter->attributes()->FormalName;
$kategorie_zpravy = "$kategorie_zpravy_pom";
//     echo $kategorie_zpravy;
//     echo '<br>';
if($je_to_pr_clanek == true) {
	$kategorie_ve_wp = 'PR články';
} else if ($druh_zpravy == "Slovensko") {
	$kategorie_ve_wp = 'Slovensko';
} else if ($druh_zpravy == "Ekonomika") {
	$kategorie_ve_wp = 'Ekonomika';
} else if ($druh_zpravy == "Svet") {
	$kategorie_ve_wp = 'Svět';
} else if ($druh_zpravy == "Šport") {
	$kategorie_ve_wp = 'Sport';
} else if ($druh_zpravy == "Kultúra") {
	$kategorie_ve_wp = 'Kultura';
} else if ($druh_zpravy == "Automagazín") {
	$kategorie_ve_wp = 'Automagazín';
} else if ($druh_zpravy == "Veda a technika") {
	$kategorie_ve_wp = 'Věda a technika';
} else if ($druh_zpravy == "Zdravie") {
	$kategorie_ve_wp = 'Zdraví';
} else {
	$kategorie_ve_wp = 'Svět';
}

//odstavce za newlines
$content['description'] = odstavce($content['description']);

//překlad z CZ do SK
$content['description'] = preloz($content['description']);

//odstavce - vyčištění
//$content['description'] = odstavce_vycisti($content['description']);

/* rozříznutí contentu description na excerpt a obsah podle počtu slov */
$content['excerpt'] = oriznout_na_pocet_slov($content['description'], 50);

$puvodni_retezec = $content['description'];
$toto_nechci = $content['excerpt'];
$nahradit_timto = "";
$content['description']=str_replace($toto_nechci, $nahradit_timto, $puvodni_retezec);

$content['excerpt'] .= '&hellip;';

$content['excerpt'] = str_replace("\r","",$content['excerpt']);
$content['excerpt'] = str_replace("\n"," ",$content['excerpt']);

$content['excerpt'] = odstavce_vycisti($content['excerpt']);

$content['description'] = '&hellip;'.$content['description'];

//výroba mezititulků
$odstavce = explode("</p><p>", $content['description']);
for ($p = 0; $p < count($odstavce); $p++) {
 $pozice_hvezdicky = stripos($odstavce[$p], "*");
 //mezititulky: 1) kratší než 60 znaků, 2) ne první odstavec, 3) neobsahuje *, 4) není to poslední odstavec
 $pocet_odstavcu = count($odstavce);
 if ((strlen($odstavce[$p]) <= 60) AND ($p > 0) AND ($pozice_hvezdicky === false) AND ($p < $pocet_odstavcu)) {
  $r = $p-1;
  $s = $p+1;
  if ((strlen($odstavce[$r]) <= 60) OR (strlen($odstavce[$s]) <= 60)) {
   $odstavce[$p] = "";
  } else {
   $odstavce[$p] = "<h3>" . $odstavce[$p] . "</h3>";
  }
 } else {
  $odstavce[$p] = "<p>" . $odstavce[$p] . "</p>";
 }
}
$content['description'] = "";
for ($p = 0; $p < count($odstavce); $p++) {
 $content['description'] = $content['description'] . $odstavce[$p];
}

//pokus o odříznutí pomlčky ze začátku
$content['excerpt'] = preg_replace("/^<p>- /s", "", $content['excerpt']);
$content['excerpt'] = preg_replace("/^- /s", "", $content['excerpt']);

//překlad z CZ do SK
$content['title'] = preloz($content['title']);

$post = array(
"title"         => $content['title'],
"description"   => $content['description'],
"mt_excerpt"    => $content['excerpt'],
"dateCreated"   => $datum_vydani,
"categories"    => array($kategorie_ve_wp),
"custom_fields" => array(
array( "key" => "image", "value" => $content['image'] )
)
);


/* podmínka zkoumá, jestli už jsme došli k postu, který byl publikovaný minule */
// při prvním běhu (prázdná databáze) podmínka nesmí platit
//if (1 == 2)
if ($content['title'] == $posledni_title)
{
	break;
} else {
	//    echo "<b>Ještě nekončíme, nedošli jsme k postu, který už byl vydán<br />";
	//    echo "Tady je proměnná content[title]:";
	//    echo $content['title'];
	//    echo "<br />Tady je proměnná posledni_title:";
	//    echo "$posledni_title </b><br />";
	/* publikování článku pomocí metaWeblog */
	if ($je_to_pr_clanek != true) {
		 //echo $content['title'] . "<br /><br />";
		 //echo $content['excerpt'] . "<br /><br />";
		 //echo $content['description'] . "<br /><br />";
		 //echo "----------odstavce---------------<br />";
		 //var_dump($odstavce);
		 //echo "<br />-------------------------<br />";

		 $client->query('metaWeblog.newPost', '', 'SITA', 'qwertzuiop123456', $post, true);
	}
	//sleep(5);
	/* kontrola, jestli vše proběhlo v pořádku */
	//    if ($client->message->faultString)
	//    {
	//     echo "Chyba - ".$client->message->faultString."<br />";
	//    } else {
	//     echo "Převedeno na článek - ".$sprava->DataContent."<br />";
	//    }
	/* uložení titlu nejposledněji vydaného článku */
	if ($pocitadlo_cyklu == 1)
	{
		$novy_posledni_title = $content['title'];
		$urcen_novy_posledni_title = true;
		//     echo "<b>Počítadlo je rovno jedné</b><br />";
		//     echo "<b>novy_posledni_title: $novy_posledni_title </b><br />";

	}
}

//  if ($pocitadlo_cyklu == 3)
//    {
//     echo "<b>Na pokusy: počítadlo je rovno třem, vyskakujeme z foreache</b>";
//     break;
//    }


}

//echo "<b>------------Konec cyklu foreach, jedeme dál-------------<b>";

//zaznamenání titlu nejnověji vydaného článku do databáze
 if ($urcen_novy_posledni_title == true)
 {
	//  echo "<b>do databáze zaznamenáváme novy_posledni_title: $novy_posledni_title </b>";

	 $smaz_to_vsechno = "DELETE FROM poslednivydany";
	 $objekt_vysledku = mysqli_query($db_spojeni, $smaz_to_vsechno);

	 if (!$objekt_vysledku)
	 {
		 echo 'Vymazání tabulky poslednivydany se nepodařilo, sorry';
		 echo '<br />';
		 echo 'Popis chyby: ', mysqli_error($db_spojeni);
		 exit();
	 }

	 $insert = "INSERT INTO poslednivydany (title)
	 VALUES ('$novy_posledni_title')";
	 $objekt_vysledku = mysqli_query($db_spojeni, $insert);

	 if (!$objekt_vysledku)
	 {
		 echo 'Poslání SQL příkazu se nepodařilo, sorry';
		 echo '<br />';
		 echo 'Popis chyby: ', mysqli_error($db_spojeni);
		 exit();
	 }
 }
// Odpojení od databáze.
if ($db_spojeni)
mysqli_close($db_spojeni);

//cesta k parseru: http://www.domain.cz/wp-content/plugins/newsml-parser/newsml-parser.php
?>