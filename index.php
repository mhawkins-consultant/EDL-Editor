<?php session_start();?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd"> 
<html>
<head>
  <title>External Data List (EDL) Editor</title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <meta name="viewport" content="width=device-width, user-scalable=no" />
  <link rel="stylesheet" href="css/style.css" type="text/css" />
</head>

<body style="background-color:#EBEBEB">

<script language="JavaScript">
<!--
function confirm_save() {
    return confirm("Are you sure you want to save your changes?");
}
function confirm_close() {
    return confirm("All unsaved changes will be lost. Are you sure you want to close this file?");
}
function confirm_dedupe() {
    return confirm("Are you sure you want to deduplicate all entries in this file?");
}
function confirm_delete() {
    return confirm("Are you sure you want to delete the selected file?");
}
function confirm_clone() {
    return confirm("Are you sure you want to clone the selected file?");
}
-->
</script>

<?php include_once("../../include/tools-header.php") ?>

<form id="form" name="form" method="post">
<table>

<tr><td colspan='10' class='green'>External Data List (EDL) Editor</td></tr>
<?php
function is_valid_name($file) {
    return preg_match('/^([-\.\w]+)$/', $file) > 0;
}

echo "<tr>";
if($_SERVER['REQUEST_METHOD']!=='POST'||(isset($_REQUEST['submit'])&&($_REQUEST['submit']=="Close"||$_REQUEST['submit']=="Delete"||$_REQUEST['submit']=='Clone'||$_REQUEST['submit']=='Create File'||$_REQUEST['submit']=='Rename File'||$_REQUEST['submit']=='Cancel'))){
    $fileList = glob(getcwd().'/data/*.txt');
    if(isset($_REQUEST['submit'])&&$_REQUEST['submit']=='Delete'){
        unlink(getcwd()."/data/".$_REQUEST['file']);
        $_SESSION['file']=basename($fileList[0]);
    }
    
    if(isset($_REQUEST['submit'])&&$_REQUEST['submit']=='Clone'){
        copy(getcwd()."/data/".$_REQUEST['file'],getcwd()."/data/Copy-".$_REQUEST['file']);
        $fileList = glob(getcwd().'/data/*.txt');
        $_SESSION['file']="Copy-".$_REQUEST['file'];
    }

    if(isset($_REQUEST['submit'])&&$_REQUEST['submit']=='Rename File'){
        rename(getcwd()."/data/".$_SESSION['file'],getcwd()."/data/".$_REQUEST['newfilename']);
        $fileList = glob(getcwd().'/data/*.txt');
        $_SESSION['file']=$_REQUEST['newfilename'];
    }

    if(isset($_REQUEST['submit'])&&$_REQUEST['submit']=='Create File'){
        touch(getcwd()."/data/".$_REQUEST['newfilename']);
        $fileList = glob(getcwd().'/data/*.txt');
        $_SESSION['file']=$_REQUEST['newfilename'];
    }
    
    echo "<td class='lgreen'>File: ";
    echo "<select name='file'>\n";
    if(!isset($_SESSION['file'])) $_SESSION['file']=basename($fileList[0]);
    foreach($fileList as $filename){
        if(is_file($filename)){
            $bName=basename($filename);
            if($_SESSION['file']!==$bName) echo "<option value='".$bName."'>".$bName."</option>\n";
            else{
                echo "<option value='".$bName."' selected>".$bName."</option>\n";
                $_SESSION['file']=$bName;
            }
        }
    }
    echo "</select></td>";
    echo '<td class="lgreen">';
    echo '<input class="submit" type="submit" name="submit" value="Open">';
//    echo '<input class="submit" type="submit" name="submit" value="Delete" onclick="return confirm_delete()">';
//    echo '<input class="submit" type="submit" name="submit" value="Clone" onclick="return confirm_clone()">';
//    echo '<input class="submit" type="submit" name="submit" value="Rename">';
//    echo '<input class="submit" type="submit" name="submit" value="New">';
    echo '</td><td class="lgreen" colspan="10"></td></tr>'."\n";
}elseif(isset($_REQUEST['submit'])&&$_REQUEST['submit']=='Rename'){
    $_SESSION['file']=$_REQUEST['file'];
    echo "<tr>";
    echo '<td class="lgreen">Rename file: '.$_REQUEST['file'].' to: <input type="text" id="newfilename" name="newfilename" value="'.$_REQUEST['file'].'">'."</td>\n";
    echo '<td class="lgreen"><input type="submit" class="submit" name="submit" value="Rename File">'."\n";
    echo '<input type="submit" class="submit" name="submit" value="Cancel">'."\n";
    echo '</td><td class="lgreen" colspan="10"></td></tr>'."\n";
}elseif(isset($_REQUEST['submit'])&&$_REQUEST['submit']=='New'){
    $_SESSION['file']=$_REQUEST['file'];
    echo "<tr>";
    echo '<td class="lgreen">New file name: <input type="text" id="newfilename" name="newfilename" value="<new file name>"></td>'."\n";
    echo '<td class="lgreen"><input type="submit" class="submit" name="submit" value="Create File">'."\n";
    echo '<input type="submit" class="submit" name="submit" value="Cancel">'."\n";
    echo '</td><td class="lgreen" colspan="10"></td></tr>'."\n";
}else{
    // editing section
    // update all lines first from page first
    if(isset($_POST['linetext'])){
        unset($_SESSION['data']);
        foreach($_POST['linetext'] as $line => $data){
            $_SESSION['data'][$line-1] = $data."\n";
        }
    }
    // do save, deletes, deduplicate and clones first
    if(isset($_REQUEST['submit'])&&$_REQUEST['submit']=='Save') {
        // save edits first
        file_put_contents(getcwd()."/data/".$_SESSION['file'], $_SESSION['data']);
    }
    // deduplicate all records
    if(isset($_REQUEST['submit'])&&$_REQUEST['submit']=='Dedupe') {
        $_SESSION['data']=array_unique($_SESSION['data']);
    }
    // natural sort all records
    if(isset($_REQUEST['submit'])&&$_REQUEST['submit']=='Natural Sort') {
        natsort($_SESSION['data']);
    }
    if(isset($data)){
        // delete line
        $count=0;
        foreach($_SESSION['data'] as $line => $data){
            $count++;
            if(isset($_POST['delete'][$count])) unset($_SESSION['data'][$line]);
        }
        // new line
        $count=0;
        foreach($_SESSION['data'] as $line => $data){
            $count++;
            if(isset($_POST['newLine'][$count])){
                $before = array_slice($_SESSION['data'], 0, $count);
                $after = array_slice($_SESSION['data'], $count);
                $_SESSION['data'] = array_merge($before,array($count=>""),$after);
                break;
            }
        }
        if(isset($_POST['newLine'][($count+1)])) array_push($_SESSION['data'],"");

        // clone line
        $count=0;
        foreach($_SESSION['data'] as $line => $data){
            $count++;
            if(isset($_POST['clone'][$count])){
                $before = array_slice($_SESSION['data'], 0, $count);
                $after = array_slice($_SESSION['data'], $count);
                $_SESSION['data'] = array_merge($before,array($count=>$data),$after);
                break;
            }
        }

        // move a line up or down
        $count=0;
        foreach($_SESSION['data'] as $line => $data){
            if(isset($_POST['up'][$count])){
                $temp=$_SESSION['data'][$count-2];
                $_SESSION['data'][$count-2]=$_SESSION['data'][$count-1];
                $_SESSION['data'][$count-1]=$temp;
                break;
            }
            if(isset($_POST['dn'][$count])){
                $temp=$_SESSION['data'][$count-1];
                $_SESSION['data'][$count-1]=$_SESSION['data'][$count];
                $_SESSION['data'][$count]=$temp;
                break;
            }
            $count++;
        }
    }

    // if we just opened the editing page then get the data from the file and populate the session data array
    if(isset($_REQUEST['submit'])&&$_REQUEST['submit']=='Open'){
        $_SESSION['file']=$_REQUEST['file'];
        $_SESSION['data'] = file(getcwd()."/data/".$_SESSION['file']);
    }
    
    // show the header
    echo '<td class="lgreen">'.'File: '.$_SESSION['file'].'</td>';
    echo '<td class="lgreen">';
    echo '<input class="submit" type="submit" name="submit" value="Save" onclick="return confirm_save()">';
    echo '<input class="submit" type="submit" name="submit" value="Close" onclick="return confirm_close()">';
    echo ' - ';
    echo '<input class="submit" type="submit" name="submit" value="Dedupe" onclick="return confirm_dedupe()">';
//    echo '<input class="submit" type="submit" name="submit" value="Alpha Sort" onclick="return confirm_alpha_sort()">';
    echo '<input class="submit" type="submit" name="submit" value="Natural Sort" onclick="return confirm_natural_sort()">';
    echo '</td><td class="lgreen" colspan="10">';
    echo 'URL: <a id="URL" href="http://'.$_SERVER['SERVER_NAME'].'/edl/data/'.$_SESSION['file'].'">http://'.$_SERVER['SERVER_NAME'].'/edl/data/'.$_SESSION['file'].'</a>';
//    echo ' <button title="Press this button to copy the password to your computer clipboard" onclick="Copy(\'URL\')">Copy URL</button>';
    echo '</td>';
    echo "</tr>\n";

    // display all the data (but stop at 10,000 lines)
    $count=0;
    foreach($_SESSION['data'] as $textperline){
        if($count>10000) break;
        $textperline=rtrim($textperline,"\n\r");
        $count++;
        //    if($count==1) continue; // skip showing the first line
        if(($count%10)==1&&$count!=1){
            echo "<tr>";
            echo "<td colspan='10' class='spacer'></td>";
            echo "</tr>";
        }
        
        echo "<tr>";
        echo '<td class="w20">'.($count)."</td>\n";
        echo "<td>".'<input type="text" id="linetext['.$count.']" name="linetext['.$count.']" value="'.$textperline.'" size="80">'."</td>\n";
        echo '<td><input type="submit" class="submit" name="delete['.$count.']" value="Delete" onclick="return confirm_delete_line('."'".$count."','".$textperline."'".')">'."\n";
        echo '<input type="submit" class="submit" name="newLine['.$count.']" value="New Line">'."\n";
        echo '<input type="submit" class="submit" name="clone['.$count.']" value="Clone">'."\n";
        if($count>1) echo '<input type="submit" class="submit" name="up['.$count.']" value="Up">'."\n";
        if($count<count($_SESSION['data'])) echo '<input type="submit" class="submit" name="dn['.$count.']" value="Down">'."\n";
        echo "</td></tr>\n";
    }
    echo '<tr>';
    echo '<td class="w20">'.($count+1)."</td>\n";
    echo '<td></td>';
    echo '<td><input type="submit" class="submit" name="newLine['.($count+1).']" value="New Line"></td>';
    echo "</tr>\n";}

// footer
echo"<tr><td colspan='10' class='spacer'></td></tr>";
//echo"<tr><td class='header' colspan='10'>Wantegrity INC. - External Data List (EDL) Editor - version 1.01a - Copyright (2008 - ".date("Y")."). All rights reserved.</td></tr>";
?>

<?php
echo "<tr><td colspan='10'  class='header'><p>Copyright &copy; ".date("Y")." Wantegrity, Inc. All rights reserved.";

$handle = fopen("counter.txt", "r");
if(!$handle){
 echo "could not open the file" ;
}
else {
 $counter = (int ) fread($handle,20);
 fclose ($handle);
 $counter++;
 echo" Hit:[". $counter . "]" ;
 $handle = fopen("counter.txt", "w" );
 fwrite($handle,$counter) ;
 fclose ($handle) ;
}

echo "</p></td></tr>";
?>
</table>
</form>
</body>
</html>

