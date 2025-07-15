<?php
// Teste completo do formulário
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>TESTE DE FORMULÁRIO COMPLETO</h1>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>Dados POST Recebidos:</h2>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    echo "<h2>Dados FILES Recebidos:</h2>";
    echo "<pre>";
    print_r($_FILES);
    echo "</pre>";
    
    // Verificar campos obrigatórios
    $campos_obrigatorios = ['nome', 'apelido', 'telefone', 'idade', 'estado_id', 'cidade_id'];
    $campos_faltando = [];
    
    foreach ($campos_obrigatorios as $campo) {
        if (empty($_POST[$campo])) {
            $campos_faltando[] = $campo;
        }
    }
    
    if (empty($campos_faltando)) {
        echo "<div style='color: green; font-weight: bold;'>✅ Todos os campos obrigatórios foram enviados!</div>";
    } else {
        echo "<div style='color: red; font-weight: bold;'>❌ Campos faltando: " . implode(', ', $campos_faltando) . "</div>";
    }
    
} else {
    ?>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="debug_test" value="<?php echo time(); ?>">
        
        <h3>Campos Obrigatórios</h3>
        <div>
            <label>Nome: <input type="text" name="nome" value="Ariel Leblanc" required></label>
        </div>
        <br>
        <div>
            <label>Apelido: <input type="text" name="apelido" value="Ariel" required></label>
        </div>
        <br>
        <div>
            <label>Telefone: <input type="text" name="telefone" value="+5547996164699" required></label>
        </div>
        <br>
        <div>
            <label>Idade: <input type="number" name="idade" value="28" min="18" required></label>
        </div>
        <br>
        <div>
            <label>Estado: 
                <select name="estado_id" required>
                    <option value="24">Santa Catarina</option>
                </select>
            </label>
        </div>
        <br>
        <div>
            <label>Cidade: 
                <select name="cidade_id" required>
                    <option value="4338">Florianópolis</option>
                </select>
            </label>
        </div>
        <br>
        <div>
            <label>Cidade Fallback: <input type="hidden" name="cidade_id_fallback" value="4338"></label>
        </div>
        
        <h3>Campos Opcionais</h3>
        <div>
            <label>WhatsApp: <input type="text" name="whatsapp" value="41999999999"></label>
        </div>
        <br>
        <div>
            <label>Gênero: 
                <select name="genero">
                    <option value="feminino">Feminino</option>
                </select>
            </label>
        </div>
        <br>
        <div>
            <label>Arquivo: <input type="file" name="arquivo_teste"></label>
        </div>
        
        <h3>Horários de Atendimento</h3>
        <div>
            <label>Segunda-feira: <input type="checkbox" name="atende[1]" value="1" checked></label>
            <input type="time" name="horario_inicio[1]" value="08:00">
            <input type="time" name="horario_fim[1]" value="23:59">
        </div>
        <br>
        <div>
            <label>Terça-feira: <input type="checkbox" name="atende[2]" value="1" checked></label>
            <input type="time" name="horario_inicio[2]" value="08:00">
            <input type="time" name="horario_fim[2]" value="23:59">
        </div>
        
        <br><br>
        <button type="submit">Enviar Teste Completo</button>
    </form>
    <?php
}
?> 