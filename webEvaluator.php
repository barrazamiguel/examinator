<?php
require("formElement.php");

class Cuestionario {
  private $campos = [];
  public $titulo = "sin titulo"; // texto de la pagina.
  private $consigna_titulo = "mmm";
  private $consigna_texto = "no hay consigna";
  private  $boton = "evaluar"; // texto del boton
  private $preguntas=0;
  private $respuestas=0;
  private $errores=[];
  private $correctores=[];
  // info del reporte:
  private $reporte = false;
  private $archivo = "report.txt";
  
  function consigna($titulo, $texto) {
    $this->consigna_titulo = $titulo;
    $this->consigna_texto = $texto;
  }
  
  public function show() {
    $web = "
<!DOCTYPE html>
<html lang='es'>
<head>
	<meta charset='UTF-8'>
	<title>$this->titulo</title>
	<meta name='viewport' content='width=device-width, initial-scale=1, shrink-to-fit=no'>
	<link rel='stylesheet' href='css/bootstrap.min.css'>
</head>
<body>
	<div class='container'>
";
    if($_POST) {
      $web.=$this->result();
    } else {
      $web.=$this->form();
    }
    $web.="
	</div>
	
	<script src='js/jquery-3.2.1.slim.min.js'></script>
	<script src='js/popper.min.js'></script>
	<script src='js/bootstrap.min.js'></script>
</body>
    </html>";
    echo $web;
  }
  
  public function form() {
    $form = "<section id='form'>";
    $form.="
<div class='row'>
	<div class='col-12'>
		<h1 class='text-center'>$this->consigna_titulo</h1>
		<p class'text-primary'>$this->consigna_texto</p>
		<hr/>
	</div>
</div>
";
    $form.="<form method='post'>";
    
    // agregamos campos:
    foreach($this->campos as $q) {
      $form.=$q;
    }
    
    $form.="
<div class='row mt-3'>
	<div class='col-12 text-center'>
		<input type='submit' class='btn' value='$this->boton'></form>
	</div>
</div>
";
    $form.="</section>";
    return $form;
  }
  
  public function result() {
    // aplicamos correctores:
    foreach($this->correctores as $c) {
      $c($this);
    }
    
    $result="
<div class='row'>
	<div class='col-12'>
<p class='text-primary text-center'>tuviste $this->respuestas de $this->preguntas respuestas correctas.</p><br><br>";

    if($this->errores) {
      $result.=$this->listaDeErrores()."<br>";
    } else {
        $result.="Felicitaciones!: todas tus respuestas fueron correctas";
	}
	
$result.="</div>
</div>";

    $result.="<div class='row mt-4'>
	<div class='col-12'>
<hr>
<footer><p>Sistema evaluativo desarrollado por Miguel Barraza</p></footer>
</div>
</div>";
    
    // guardamos reporte:
    if($this->reporte) {
      $this->guardarReporte();
    }
    return $result;
  }
  
  public function listaDeErrores() {
    $errores = "<h2>Errores</h2><ol>";
    foreach($this->errores as $er) {
      $errores.="<li>$er</li>";
    }
    $errores.="</ol>";
    return $errores;
  }
  
  public function sumarRespuestaCorrecta() {
    $this->respuestas++;
  }
  
  public function agregarError($mensajeError) {
    $this->errores[] = $mensajeError;
  }
  
  public function guardarReporte() {
    $informe="reporte:\n";
    $informe .= "fecha ". date("Y/m/d")."\n";
    $informe .= "hora: ". date("G:i:s"). "\n";
    
    foreach($_POST as $a => $b) {
      $informe .= "$a: $b\n";
    }
    
    $informe .= "tubo $this->respuestas de $this->preguntas respuestas correctas.\n\n";
    if($this->errores) {
      $informe .= "Errores\n";
      foreach($this->errores as $er) {
        $informe .= html_entity_decode($er)."\n";
      }
      $informe .= "\n";
    }
    
    // guardamos en archivo:
    file_put_contents($this->archivo, $informe, FILE_APPEND | LOCK_EX);
  }
  
  public function configurarReporte($nombre) {
    $this->reporte = true;
    $this->archivo = $nombre;
  }
  
  public function campo($name, $text, $label) {
    $this->campos[] = new InputText($name, $text, $label);
  }
  
  function seleccionar($name, $text, $list, $correct, $error) {
    $this->campos[] = new InputRadio($name, $text, $list, $correct);
    $this->preguntas++;
    $this->correctores[] = new Verificar($name, $correct, $error);
  }
  
  function lista($name, $text, $list, $correct, $error) {
    $this->campos[] = new InputSelect($name, $text, $list, $correct);
    $this->preguntas++;
    $this->correctores[] = new Verificar($name, $correct, $error);
  }
  
  public function escribir($name, $text, $correct, $error) {
    $this->campos[] = new InputText($name, $text, "escriba el código");
    $this->preguntas++;
    $this->correctores[] = new Verificar($name, $correct, $error.": ".htmlentities($correct));
  
  }
}

?>