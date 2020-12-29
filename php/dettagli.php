<?php
    require_once 'dbConnection.php';
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

    $idbirra = $_GET["id"];

    //preleva info da database
    try{
        if(!$idbirra)
            throw new Exception("No id value found");

        $query = "SELECT * FROM birre WHERE id=".$idbirra;
        $birra = DBAccess::query($query)[0];

        //mostra pagina notfound se id non esiste
        if(empty($birra))
            throw new Exception("No id value found");

        // verifica se presenti parametri di inserimento/rimozione recensione solo se utente è loggato
        //eliminazione recensione
        if(isset($_SESSION['id']) && !empty($_POST['removeid']))
        {
            //verifica che la recensione stia venendo eliminata dall'autore
            $author=DBAccess::query("SELECT username FROM recensioni, utenti WHERE recensioni.utente=utenti.id AND recensioni.id=".$_POST['removeid'])[0]["username"];
            if( (isset($_SESSION['admin']) && $_SESSION['admin']==1)  || (!empty($author) && $author==$_SESSION['id']) )
            {
                DBAccess::command("DELETE FROM recensioni WHERE id=".$_POST['removeid']);
                //$_SESSION['msg']="ok";
                //redirect to self per resettare parametri post, parametro msg=OK per visualizzare messaggio di successo
                header('Location: '.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']."&msg=OK");
            }
            else
                header('Location: '.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']."&msg=NO");
        }
        //inserimento recensione
        else if (isset($_SESSION['id']) && !empty($_POST['review']) && !empty($_POST['rating'])){
            //sanitizzazione review + rating
            $newreview=filter_var($_POST['review'], FILTER_SANITIZE_STRING);
            $rating=filter_var($_POST['rating'], FILTER_SANITIZE_STRING);

            //prelievo id utente per query di inserimento
            $userid=DBAccess::query("SELECT id FROM utenti WHERE username=\"{$_SESSION['id']}\"")[0]["id"];

            //inserimento in database
            if(!empty($userid) && !empty($newreview) && !empty($rating) && is_numeric($rating) && $rating>=0 && $rating<=10){
                //inserimento recensione
                DBAccess::command("INSERT INTO recensioni (id, utente, birra, descrizione, voto) VALUES (NULL, {$userid},  {$idbirra}, \"{$newreview}\", {$rating})");
                //$_SESSION['msg']="ok";
                //redirect to self per resettare parametri post, parametro msg=OK per visualizzare messaggio di successo
                header('Location: '.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']."&msg=OK");
            }
            //redirect 
            else{
                header('Location: '.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']."&msg=NO");
            }
        }   


        //preleva recensioni della birra
        $query = 'SELECT recensioni.id AS revid, recensioni.descrizione, recensioni.voto, utenti.username FROM recensioni, utenti WHERE recensioni.birra='.$idbirra.' AND recensioni.utente=utenti.id';
        $recensioni = DBAccess::query($query);

    } catch (Exception $e) {
        //Redirect a pagina errore
        $_SESSION['msg']=$e->getMessage();
        header('Location: notfound.php');
    }

    //breadcrumbs
    $path=[
        "Home" => "<root/>php/home.php",
        "Prodotti" => "<root/>php/prodotti.php",
        $birra["nome"] => "active",
    ];
    
    //Costruisco pagina
    $paginaHTML = file_get_contents('../html/dettagli.html');
    $paginaHTML = str_replace("<head/>", htmlMaker::makeHead($birra["nome"]." - La tana del Luppolo"), $paginaHTML);
    $paginaHTML = str_replace("<keywords/>", ", ".$birra["nome"], $paginaHTML); 
    $paginaHTML = str_replace("<bc/>", htmlMaker::makeBreadCrumbs($path), $paginaHTML);
    $paginaHTML = str_replace("<header/>", htmlMaker::makeHeader($username), $paginaHTML);
    $paginaHTML = str_replace("<tornasu/>", htmlMaker::makeTornaSu(), $paginaHTML);
    $paginaHTML = str_replace("<footer/>", htmlMaker::makeFooter(), $paginaHTML);
    $paginaHTML = str_replace("<beerinfo/>", htmlMaker::beerInfo($birra), $paginaHTML);

    //stampa recensioni
    if(strpos($paginaHTML, "<reviews/>")!==false)
    {
        if($recensioni==null)
            $paginaHTML = str_replace("<reviews/>", 'Nessuno ha ancora recensito questa birra.', $paginaHTML);
        else
            $paginaHTML = str_replace("<reviews/>", htmlMaker::beerReview($recensioni), $paginaHTML);
    }

    if(strpos($paginaHTML, "<beerid/>")!==false)
                $paginaHTML = str_replace("<beerid/>", $idbirra, $paginaHTML);

    //stampa messaggio di risultato query
    //if(isset($_SESSION['msg']) && $_SESSION['msg']=="ok")
    if(isset($_GET['msg']))
    {
        if($_GET['msg']=="OK")
            $paginaHTML = str_replace("<msg/>", '<div id="msgsuccess">Operazione eseguita con successo!</div>', $paginaHTML);
        else
            $paginaHTML = str_replace("<msg/>", '<div id="msgfail">Operazione fallita.</div>', $paginaHTML);
        //unset($_SESSION['msg']);
    }
    else
        $paginaHTML = str_replace("<msg/>", "", $paginaHTML);

    $paginaHTML = str_replace("<root/>", "../", $paginaHTML);
    echo $paginaHTML;


?>