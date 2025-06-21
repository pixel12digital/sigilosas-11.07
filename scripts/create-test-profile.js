require('dotenv').config({ path: '.env.local' });
const { createClient } = require('@supabase/supabase-js');

const supabaseUrl = process.env.NEXT_PUBLIC_SUPABASE_URL;
const supabaseServiceKey = process.env.SUPABASE_SERVICE_ROLE_KEY;

if (!supabaseUrl || !supabaseServiceKey) {
  console.error("Erro: As variáveis de ambiente do Supabase não estão configuradas.");
  process.exit(1);
}

const supabaseAdmin = createClient(supabaseUrl, supabaseServiceKey);
const API_URL = 'http://localhost:3000/api/cadastro';

async function criarPerfilDeTeste() {
  const testEmail = `perfil-teste-${Date.now()}@sigilosas.test`;
  const testPassword = 'password123';
  const testNome = 'Juliana (Perfil de Teste)';
  let userId = null;

  console.log(`Iniciando criação de perfil de teste com o e-mail: ${testEmail}`);

  try {
    console.log("Buscando uma cidade válida para o teste...");
    const { data: cidade, error: cidadeError } = await supabaseAdmin
      .from('cidades')
      .select('id, nome')
      .limit(1)
      .single();

    if (cidadeError || !cidade) {
      throw new Error("Não foi possível encontrar uma cidade para usar no teste.");
    }
    console.log(`Usando cidade: ${cidade.nome} (ID: ${cidade.id})`);

    const formData = {
      email: testEmail,
      senha: testPassword,
      nome: testNome,
      telefone: '11999998888',
      idade: 24,
      genero: 'F',
      cidade_id: cidade.id,
      descricao: 'Este é um perfil de teste permanente criado para visualização no painel.',
    };

    console.log(`Enviando requisição para a API de cadastro...`);
    const response = await fetch(API_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(formData),
    });

    const responseData = await response.json();

    if (!response.ok || !responseData.success) {
      console.error("A API retornou um erro:", responseData.error);
      throw new Error(`A API falhou com status ${response.status}.`);
    }

    userId = responseData.data.user.id;
    console.log(`API retornou sucesso! ID do usuário: ${userId}`);

    console.log("\n✅ SUCESSO! O perfil de teste foi criado e permanecerá no banco de dados.");
    console.log("Você já pode visualizá-lo no seu painel de administração.");
    console.log(`E-mail para login (se necessário): ${testEmail}`);
    console.log(`Senha: ${testPassword}`);

  } catch (error) {
    console.error("\n❌ FALHA AO CRIAR PERFIL DE TESTE. Causa do erro:");
    console.error(error.message || error);
  }
}

criarPerfilDeTeste(); 