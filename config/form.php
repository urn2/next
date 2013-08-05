<?php
/*
$form = new DooForm(array(
    'method' => 'post',
    'action' => $action,
    'elements' => array(
        'username' => array('text', array(
            'required' => true,
            'label' => 'My username: ',
            'attributes' => array("style" => 'border:1px solid #000;', 'class' => 'username-field'),
            'field-wrapper' => 'div',
            'validators' => array(
                array('username',4,7),
                array('maxlength',6,'This is too long'),
                array('minlength',6)
             )
        ))
    )
));


$mail = new DooMailer();
 $mail->addTo('yyyyy@yyyyy.yyy');
 $mail->addTo('xxxx@xxxx.xxx', 'John Smith');
 $mail->setSubject("This is test subject!");
 $mail->setBodyText("This is plain text body");
 $mail->setBodyHtml("<b>This is HTML body!</b>");
$mail->addAttachment('/var/web/files/file1.jpg');
 $mail->addAttachment('/var/web/files/file2.zip');
 $mail->setFrom('doo@xxxxxxxx.xxx', 'DooPHP ');
 $mail->send();

Doo::loadHelper('DooGdImage');
//upload/source path, and output saved path
$gd = new DooGdImage('/var/www/uploaded/', '/var/www/resized_pic/');
 
$uploadImg = $gd->uploadImage('profile_pic', 'img_' .date('Ymdhis'));


$gd->generatedQuality = 85;
$uploadImage->generatedType="jpg";

//This thumbnail is 46x46 pixels, resize adaptively (perfect 46x46 crop from center)
//Pic name is img_201001011200_46x46.jpg
$gd->thumbSuffix = '_46x46';
$gd->adaptiveResize($uploadImg,46,46);

//Resize propotionally (so will not be perfect 75x75 depends on the image ratio, no cropping done)
//Pic name is img_201001011200_75x75.jpg
$gd->thumbSuffix = '_75x75';
$gd->createThumb($uploadImg, 75, 75);

http://learn.doophp.com/category/tutorials/
*/
/*
//$this->db()->find('User');&nbsp; is the same
Doo::db()->find('User');

//search for one record
Doo::db()->find('User', array('limit'=>1) );

//search for a user named 'david'
Doo::db()->find('User', array('where'=>"uname='david'", 'limit'=>1) );

//using prepared statement to avoid sql injection
Doo::db()->find('User', array(
                    'where' => 'user=?',
                   &nbsp;'param' => array($_GET['name']),
                   &nbsp;'limit' => 1
               &nbsp;)
          &nbsp;);

//Or simply use this for shorter code.
Doo::loadModel('User');
$u = new User;
$u->uname = $_GET['name'];
$result = Doo::db()->find($u, array('limit'=>1));
*/
 