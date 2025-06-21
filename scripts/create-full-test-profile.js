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

async function criarPerfilCompleto() {
  console.log('Iniciando criação de perfil de teste COMPLETO...');
  try {
    const { data: cidade } = await supabaseAdmin.from('cidades').select('id, nome').limit(1).single();
    if (!cidade) throw new Error('Nenhuma cidade encontrada para o teste.');

    const formData = {
      email: `carla-teste-${Date.now()}@sigilosas.test`,
      senha: 'password123',
      nome: 'Carla (Teste Completo)',
      telefone: '11977776666',
      idade: 27,
      genero: 'F',
      cidade_id: cidade.id,
      descricao: 'Perfil completo com mídias para teste no painel.',
      foto: 'https://images.pexels.com/photos/3764014/pexels-photo-3764014.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1',
      galeria_fotos: [
        'https://images.pexels.com/photos/3775087/pexels-photo-3775087.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1',
        'https://images.pexels.com/photos/2613260/pexels-photo-2613260.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1'
      ],
      video_url: 'https://www.w3schools.com/html/mov_bbb.mp4'
    };

    console.log(`Enviando requisição para ${API_URL}...`);
    const response = await fetch(API_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(formData),
    });

    const responseData = await response.json();
    if (!response.ok || !responseData.success) {
      throw new Error(responseData.error?.details || responseData.error?.message || 'A API retornou um erro desconhecido.');
    }
    
    console.log('\\n✅ SUCESSO! Perfil de teste COMPLETO criado.');
    console.log('Verifique o painel para ver "Carla (Teste Completo)" com suas mídias.');

  } catch (error) {
    console.error('\\n❌ FALHA AO CRIAR PERFIL COMPLETO:', error.message);
  }
}

criarPerfilCompleto(); 