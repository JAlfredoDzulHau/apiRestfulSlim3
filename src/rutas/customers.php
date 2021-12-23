<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app = new \Slim\App;

// GET Obtener Todos los clientes 
$app->get('/api/clientes', function(Request $request, Response $response){
  $sql = "SELECT c.nombre, c.apellido, c.direccion, h.description AS 'Descripcion', r.description
  FROM customers c
  INNER JOIN communes h
  ON c.id_com = h.id_com
  INNER JOIN regions r
  ON h.id_reg = r.id_reg
  WHERE estado = 'A'";
  //$sql = "SELECT * FROM customers"; 
  
  try{

    //Instanciamos la base de datos
    $db = new db();

    //conexión
    $db = $db->conectDB();
    $resultado = $db->query($sql);

    if ($resultado->rowCount() > 0){
      $clientes = $resultado->fetchAll(PDO::FETCH_OBJ);
      //Exportamos y mostramos en formato JSON
      echo json_encode($clientes);
      echo '{"TRUE"}';
    }else {
      echo json_encode("No existen clientes en la BBDD.");
      echo '{"FALSE"}';
    }
    $resultado = null;
    $db = null;
  }catch(PDOException $e){
    echo '{"error" : {"text":'.$e->getMessage().'}';
  }
}); 

// GET Recuperar cliente por ID 
$app->get('/api/clientes/{dni}', function(Request $request, Response $response){
  $dni_cliente = $request->getAttribute('dni');
  $sql = "SELECT * FROM customers WHERE dni = $dni_cliente";
  try{
    $db = new db();
    $db = $db->conectDB();
    $resultado = $db->query($sql);

    if ($resultado->rowCount() > 0){
      $cliente = $resultado->fetchAll(PDO::FETCH_OBJ);
      echo json_encode($cliente);
      echo '{"TRUE"}';
    }else {
      echo json_encode("No existen cliente en la BBDD con este DNI.");
      echo '{"FALSE"}';
    }
    $resultado = null;
    $db = null;
  }catch(PDOException $e){
    echo '{"error" : {"text":'.$e->getMessage().'}';
  }
});



// POST Crear nuevo cliente 
$app->post('/api/clientes/nuevo', function(Request $request, Response $response){
  $dni = $request->getParam('dni');
  $id_reg = $request->getParam('id_reg');
  $id_com = $request->getParam('id_com');
  $email = $request->getParam('email');
  $nombre = $request->getParam('nombre');
  $apellido = $request->getParam('apellido');
  $direccion = $request->getParam('direccion'); 
  $date_reg = $request->getParam('date_reg');
  $estado = $request->getParam('estado');   
 
 $sql = "INSERT INTO customers (dni, id_reg, id_com, email, nombre, apellido, direccion, date_reg, estado) VALUES 
         (:dni, :id_reg, :id_com, :email, :nombre, :apellido, :direccion, :date_reg, :estado) ";
 try{
   $db = new db();
   $db = $db->conectDB();
   $resultado = $db->prepare($sql);


   $resultado->bindParam(':dni', $dni);
   $resultado->bindParam(':id_reg', $id_reg);
   $resultado->bindParam(':id_com', $id_com);
   $resultado->bindParam(':email', $email);
   $resultado->bindParam(':nombre', $nombre);
   $resultado->bindParam(':apellido', $apellido);
   $resultado->bindParam(':direccion', $direccion);
   $resultado->bindParam(':date_reg', $date_reg);
   $resultado->bindParam(':estado', $estado);

   //if(!$resultado->execute()){
    //echo json_encode("Cliente  NO guardado.");  
   //}else{
    //echo json_encode("Nuevo cliente guardado.");
   //}
   
  $resultado->execute();
   echo json_encode("Nuevo cliente guardado.");  
   echo '{"TRUE"}';

   $resultado = null;
   $db = null;
 }catch(PDOException $e){
   echo '{"error" : {"text":'.$e->getMessage().'}';
   echo '{"FALSE"}';
   
 }
}); 


// DELETE borrar cliente 
$app->delete('/api/clientes/delete/{dni}', function(Request $request, Response $response){
  $dni_cliente = $request->getAttribute('dni');
  $sql = "DELETE FROM customers WHERE dni = $dni_cliente AND estado != 'trash'"; 
     
 try{
   $db = new db();
   $db = $db->conectDB();
   $resultado = $db->prepare($sql);
    $resultado->execute();

   if ($resultado->rowCount() > 0) {
     echo json_encode("Cliente eliminado.");
     echo '{"TRUE"}';  
   }else {
     echo json_encode("Registro no existe");
     echo '{"FALSE"}';
   }

   $resultado = null;
   $db = null;
 }catch(PDOException $e){
   echo '{"error" : {"text":'.$e->getMessage().'}';
 }
}); 

//key de autenticación a traves de middlewares

?>