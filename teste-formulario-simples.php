<?php
// Teste simples do formulário
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>TESTE DE FORMULÁRIO SIMPLES</h1>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>Dados POST Recebidos:</h2>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    echo "<h2>Dados FILES Recebidos:</h2>";
    echo "<pre>";
    print_r($_FILES);
    echo "</pre>";
} else {
    ?>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="debug_test" value="<?php echo time(); ?>">
        
        <div>
            <label>Nome: <input type="text" name="nome" value="Teste Nome"></label>
        </div>
        
        <div>
            <label>Apelido: <input type="text" name="apelido" value="Teste Apelido"></label>
        </div>
        
        <div>
            <label>Telefone: <input type="text" name="telefone" value="41999999999"></label>
        </div>
        
        <div>
            <label>Idade: <input type="number" name="idade" value="25"></label>
        </div>
        
        <div>
            <label>Estado: 
                <select name="estado_id">
                    <option value="24">Santa Catarina</option>
                </select>
            </label>
        </div>
        
        <div>
            <label>Cidade: 
                <select name="cidade_id">
                    <option value="4338">Florianópolis</option>
                </select>
            </label>
        </div>
        
        <div>
            <label>Arquivo: <input type="file" name="arquivo_teste"></label>
        </div>
        
        <button type="submit">Enviar Teste</button>
    </form>
    <?php
}
?> 