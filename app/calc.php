<?php
require_once dirname(__FILE__).'/../config.php';

// KONTROLER strony kalkulatora

// W kontrolerze niczego nie wysyła się do klienta.
// Wysłaniem odpowiedzi zajmie się odpowiedni widok.
// Parametry do widoku przekazujemy przez zmienne.

//ochrona kontrolera - poniższy skrypt przerwie przetwarzanie w tym punkcie gdy użytkownik jest niezalogowany
include _ROOT_PATH.'/app/security/check.php';

//pobranie parametrów
function getParams(&$x,&$y,&$z,&$operation){
	$x = isset($_REQUEST['x']) ? $_REQUEST['x'] : null;
	$y = isset($_REQUEST['y']) ? $_REQUEST['y'] : null;
        $z = isset($_REQUEST['z']) ? $_REQUEST['z'] : null;
	$operation = isset($_REQUEST['op']) ? $_REQUEST['op'] : null;	
}

//walidacja parametrów z przygotowaniem zmiennych dla widoku
function validate(&$x,&$y,&$z,&$operation,&$messages){
	// sprawdzenie, czy parametry zostały przekazane
	if ( ! (isset($x) && isset($y) && isset($z) && isset($operation))) {
		// sytuacja wystąpi kiedy np. kontroler zostanie wywołany bezpośrednio - nie z formularza
		// teraz zakładamy, ze nie jest to błąd. Po prostu nie wykonamy obliczeń
		return false;
	}

	// sprawdzenie, czy potrzebne wartości zostały przekazane
	if ( $x == "") {
		$messages [] = 'Nie podano kwoty';
	}
	if ( $y == "") {
		$messages [] = 'Nie podano liczby lat';
	}
        
        if ( $z == "") {
		$messages [] = 'Nie podano liczby oprocentowania';
	}

	//nie ma sensu walidować dalej gdy brak parametrów
	if (count ( $messages ) != 0) return false;
	
	// sprawdzenie, czy $x i $y są liczbami całkowitymi
	if (! is_numeric( $x )) {
		$messages [] = 'Pierwsza wartość nie jest liczbą';
	}
	
	if (! is_numeric( $y )) {
		$messages [] = 'Druga wartość nie jest liczbą';
	}	
        
        if (! is_numeric( $z )) {
		$messages [] = 'Trzecia wartość nie jest liczbą';
	}	

	if (count ( $messages ) != 0) return false;
	else return true;
}

function process(&$x,&$y,&$z,&$operation,&$messages,&$result){
	global $role;
	
	//konwersja parametrów na int
	$x = floatval($x);
	$y = floatval($y);
        $z = floatval($z);
	
	//wykonanie operacji
	switch ($operation) {
		case 'kredyt' :
			if ($role == 'admin'){
                                $zysk = ($x * $z)/100;
                                $suma = $x + $zysk;
                                $miesiace = $y * 12; 
                            
				$result = $suma / $miesiace;
			} else {
				$messages [] = 'Tylko administrator może liczyc kredyt !';
			}
			break;
		case 'lokata' :
                                $zysk = ($x * $z)/100;
                                $suma = $x + $zysk;                          
				$zyskroczny = $suma / $y; 
                                $kwotanarok = $x / $y;
                                $result = $zyskroczny - $kwotanarok;
			break;
		default :
			$result = 12;
			break;
	}
}

//definicja zmiennych kontrolera
$x = null;
$y = null;
$z = null;
$operation = null;
$result = null;
$messages = array();

//pobierz parametry i wykonaj zadanie jeśli wszystko w porządku
getParams($x,$y,$z,$operation);
if ( validate($x,$y,$z,$operation,$messages) ) { // gdy brak błędów
	process($x,$y,$z,$operation,$messages,$result);
}

// Wywołanie widoku z przekazaniem zmiennych
// - zainicjowane zmienne ($messages,$x,$y,$operation,$result)
//   będą dostępne w dołączonym skrypcie
include 'calc_view.php';