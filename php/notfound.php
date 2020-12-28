<?php
    require_once 'htmlMaker.php';
    session_start();
    //controllo se minorenne
    if(!isset($_SESSION['adult']) || !$_SESSION['adult'])
        header('Location: ageverification.php');

    //controllo se loggato
    $username = "";
    if(isset($_SESSION['id'])){
        $username = $_SESSION['id'];
    }
    
    //Costruisco pagina
    $paginaHTML = file_get_contents('../html/notfound.html');
    $paginaHTML = str_replace("<head/>", htmlMaker::makeHead("Non trovato - La tana del Luppolo"), $paginaHTML);
    $paginaHTML = str_replace("<keywords/>", "", $paginaHTML); 
    $paginaHTML = str_replace("<header/>", htmlMaker::makeHeader($username), $paginaHTML);
    $paginaHTML = str_replace("<footer/>", htmlMaker::makeFooter(), $paginaHTML);
    $paginaHTML = str_replace("<error/>", htmlMaker::makeNotfound(), $paginaHTML);
    $paginaHTML = str_replace("<root/>", "../", $paginaHTML);
    $paginaHTML = str_replace("<root/>", "../", $paginaHTML);
    echo $paginaHTML;


?>