<?php
/**
 * Teste de Sessões - Verificar isolamento entre admin e acompanhante
 */

echo "<h1>Teste de Sessões - Sigilosas VIP</h1>";

// Teste 1: Sessão Admin
echo "<h2>1. Teste Sessão Admin</h2>";
session_name('sigilosas_admin_session');
session_start();
echo "<p>Sessão Admin - Nome: " . session_name() . "</p>";
echo "<p>Sessão Admin - ID: " . session_id() . "</p>";
echo "<p>Sessão Admin - user_id: " . ($_SESSION['user_id'] ?? 'NÃO DEFINIDO') . "</p>";
session_write_close();

// Teste 2: Sessão Acompanhante
echo "<h2>2. Teste Sessão Acompanhante</h2>";
session_name('sigilosas_acompanhante_session');
session_start();
echo "<p>Sessão Acompanhante - Nome: " . session_name() . "</p>";
echo "<p>Sessão Acompanhante - ID: " . session_id() . "</p>";
echo "<p>Sessão Acompanhante - acompanhante_id: " . ($_SESSION['acompanhante_id'] ?? 'NÃO DEFINIDO') . "</p>";
session_write_close();

// Teste 3: Verificar se as sessões são independentes
echo "<h2>3. Verificação de Isolamento</h2>";
session_name('sigilosas_admin_session');
session_start();
$admin_session_id = session_id();
$admin_user_id = $_SESSION['user_id'] ?? 'NÃO DEFINIDO';
session_write_close();

session_name('sigilosas_acompanhante_session');
session_start();
$acompanhante_session_id = session_id();
$acompanhante_id = $_SESSION['acompanhante_id'] ?? 'NÃO DEFINIDO';
session_write_close();

echo "<p><strong>Admin Session ID:</strong> " . $admin_session_id . "</p>";
echo "<p><strong>Acompanhante Session ID:</strong> " . $acompanhante_session_id . "</p>";
echo "<p><strong>Sessões são diferentes:</strong> " . ($admin_session_id !== $acompanhante_session_id ? 'SIM ✓' : 'NÃO ✗') . "</p>";

echo "<h2>4. Links de Teste</h2>";
echo "<p><a href='admin/login.php' target='_blank'>Login Admin</a></p>";
echo "<p><a href='pages/login-acompanhante.php' target='_blank'>Login Acompanhante</a></p>";
echo "<p><a href='acompanhante/teste.php' target='_blank'>Teste Painel Acompanhante</a></p>";

echo "<h2>5. Status das Sessões</h2>";
echo "<p><strong>Admin logado:</strong> " . ($admin_user_id !== 'NÃO DEFINIDO' ? 'SIM' : 'NÃO') . "</p>";
echo "<p><strong>Acompanhante logada:</strong> " . ($acompanhante_id !== 'NÃO DEFINIDO' ? 'SIM' : 'NÃO') . "</p>";
?> 