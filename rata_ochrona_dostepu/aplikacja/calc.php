<?php
require_once dirname(__FILE__).'/../config.php';

// KONTROLER strony kalkulatora

// W kontrolerze niczego nie wysyła się do klienta.
// Wysłaniem odpowiedzi zajmie się odpowiedni widok.
// Parametry do widoku przekazujemy przez zmienne.

//ochrona kontrolera - poniższy skrypt przerwie przetwarzanie w tym punkcie gdy użytkownik jest niezalogowany
include _ROOT_PATH.'/aplikacja/security/check.php';

//pobranie parametrów
function getParams(&$rata){
	$rata['kwota'] = isset($_REQUEST['kwota']) ? $_REQUEST['kwota'] : null;
	$rata['oprocentowanie'] = isset($_REQUEST['oprocentowanie']) ? $_REQUEST['oprocentowanie'] : null;
	$rata['lata'] = isset($_REQUEST['lata']) ? $_REQUEST['lata'] : null;	
}

//walidacja parametrów z przygotowaniem zmiennych dla widoku
function validate(&$rata, &$messages){
	// sprawdzenie, czy parametry zostały przekazane
	if ( ! (isset($rata['kwota']) && isset($rata['oprocentowanie']) && isset($rata['lata']))) {
		// sytuacja wystąpi kiedy np. kontroler zostanie wywołany bezpośrednio - nie z formularza
		// teraz zakładamy, ze nie jest to błąd. Po prostu nie wykonamy obliczeń
		return false;
	}

	// sprawdzenie, czy potrzebne wartości zostały przekazane
    if ( $rata['kwota'] == "") {
	    $messages [] = 'Nie podano kwoty';
    }
    if ( $rata['lata'] == "") {
	    $messages [] = 'Nie podano ilości lat';
    }

	//nie ma sensu walidować dalej gdy brak parametrów
	if (count ( $messages ) != 0) return false;
	
	// sprawdzenie, czy $x i $y są liczbami całkowitymi
	if (! is_numeric( $rata['kwota'] )) {
		$messages [] = 'Kwota nie jest liczbą całkowitą';
	}
	
	if (! is_numeric( $rata['lata'] )) {
		$messages [] = 'Ilość lat nie jest liczbą całkowitą';
	}	

	if (count ( $messages ) != 0) return false;
	else return true;
}

function process(&$rata,&$messages,&$result){
	global $role;
	
	//konwersja parametrów na int
	$kwota = intval($rata['kwota']);
	$lata = intval($rata['lata']);
    $oprocentowanie = intval($rata['oprocentowanie']);
    $oprocentowanie = $oprocentowanie/100;
	
	//wykonanie operacji

    if($kwota>50000){
        if($role=='admin'){
	        $result=($kwota+($kwota*$oprocentowanie))/$lata;
            $result=$result/12;
        }else{
            $messages [] = "Tylko administrator moze liczyć kwoty ponad 50000!";
        }
    }else{
        $result=($kwota+($kwota*$oprocentowanie))/$lata;
        $result=$result/12;
    }

    
}


//definicja zmiennych kontrolera
$kwota = null;
$lata = null;
$oprocentowanie = null;
$result = null;
$messages = array();

//pobierz parametry i wykonaj zadanie jeśli wszystko w porządku
getParams($rata);
if ( validate($rata,$messages) ) { // gdy brak błędów
	process($rata,$messages,$result);
}

// Wywołanie widoku z przekazaniem zmiennych
// - zainicjowane zmienne ($messages,$x,$y,$operation,$result)
//   będą dostępne w dołączonym skrypcie
include 'calc_view.php';