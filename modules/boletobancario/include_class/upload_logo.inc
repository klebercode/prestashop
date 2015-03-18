<?

$arquivo = $_FILES['logotipo'];

$config = array();
$config["tamanho"] = 106883;
$config["largura"] = 200;
$config["altura"]  = 100;
$config["diretorio"] = dirname(dirname(__file__)) . "\imagens\\";


function nome($extensao)
{
    global $config;

    // Gera um nome único para a imagem
    $temp = substr(md5(uniqid(time())), 0, 10);
    $imagem_nome = $temp . "." . $extensao;

    // Verifica se o arquivo já existe, caso positivo, chama essa função novamente
    if (file_exists($config["diretorio"] . $imagem_nome)) {
        $imagem_nome = nome($extensao);
    }

    return $imagem_nome;
}

if ($arquivo) {
    $erro = array();

    if (!eregi("^image\/(pjpeg|png|x-png)$", $arquivo["type"])) {
       	 	$erro[] = "Arquivo em formato inválido! A imagem deve ser em formato png. Envie outro arquivo";
    } else {
        if ($arquivo["size"] > $config["tamanho"]) {
            $erro[] = "Arquivo em tamanho muito grande! A imagem deve ser de no máximo " . $config["tamanho"] .
                " bytes. Envie outro arquivo";
        }

        $tamanhos = getimagesize($arquivo["tmp_name"]);

        if ($tamanhos[0] > $config["largura"]) {
            $erro[] = "Largura da imagem não deve ultrapassar " . $config["largura"] .
                " pixels";
        }

        if ($tamanhos[1] > $config["altura"]) {
            $erro[] = "Altura da imagem não deve ultrapassar " . $config["altura"] .
                " pixels";
        }
    }

    if (!sizeof($erro)) {
        preg_match("/\.(gif|bmp|png|jpg|jpeg){1}$/i", $arquivo["name"], $ext);

        //$imagem_nome = nome($ext[1]);
        $imagem_nome 	= "logo_empresa";

        $imagem_dir 	= $config["diretorio"] . $imagem_nome .'.png';
		
        if (!move_uploaded_file($arquivo["tmp_name"], $imagem_dir))
            $erro[] = 'Impossível substituir a imagem do logo';
		
		//move_uploaded_file($_FILES['img_name']['tmp_name'], $path);
    }
}

if (sizeof($erro)) {
    echo "<font color=\"red\"><b><u>Ocorreu(am) o(s) seguinte(s) erro(s):</u><b></font> <br>";
    foreach ($erro as $err) {
        echo " * " . htmlentities($err) . "<br>";
    }

}

?>