const { createClient } = require('@supabase/supabase-js');
const mysql = require('mysql2/promise');

// Configurações do Supabase
const supabaseUrl = process.env.SUPABASE_URL;
const supabaseServiceKey = process.env.SUPABASE_SERVICE_ROLE_KEY;

// Configurações do MySQL (seu banco atual)
const mysqlConfig = {
  host: 'localhost',
  user: 'root',
  password: '',
  database: 'sigilosas'
};

const supabase = createClient(supabaseUrl, supabaseServiceKey);

async function migrateData() {
  let mysqlConnection;
  
  try {
    console.log('🔄 Iniciando migração de dados...');
    
    // Conectar ao MySQL
    mysqlConnection = await mysql.createConnection(mysqlConfig);
    console.log('✅ Conectado ao MySQL');
    
    // Migrar cidades
    console.log('📦 Migrando cidades...');
    const [cidades] = await mysqlConnection.execute('SELECT * FROM cidades');
    for (const cidade of cidades) {
      const { error } = await supabase
        .from('cidades')
        .upsert({ id: cidade.id, nome: cidade.nome });
      
      if (error) {
        console.error('❌ Erro ao migrar cidade:', cidade.nome, error);
      }
    }
    console.log(`✅ ${cidades.length} cidades migradas`);
    
    // Migrar acompanhantes
    console.log('📦 Migrando acompanhantes...');
    const [acompanhantes] = await mysqlConnection.execute('SELECT * FROM acompanhantes');
    for (const acompanhante of acompanhantes) {
      const { error } = await supabase
        .from('acompanhantes')
        .upsert({
          id: acompanhante.id,
          nome: acompanhante.nome,
          cidade_id: acompanhante.cidade_id,
          idade: acompanhante.idade,
          genero: acompanhante.genero,
          valor: acompanhante.valor,
          descricao: acompanhante.descricao,
          destaque: Boolean(acompanhante.destaque),
          data_cadastro: acompanhante.data_cadastro,
          status: acompanhante.status || 'pendente',
          disponibilidade: acompanhante.disponibilidade,
          verificado: Boolean(acompanhante.verificado),
          bairro: acompanhante.bairro,
          aceita_cartao: Boolean(acompanhante.aceita_cartao),
          atende_casal: Boolean(acompanhante.atende_casal),
          local_proprio: Boolean(acompanhante.local_proprio),
          aceita_pix: Boolean(acompanhante.aceita_pix),
          genitalia: acompanhante.genitalia,
          preferencia_sexual: acompanhante.preferencia_sexual,
          peso: acompanhante.peso,
          altura: acompanhante.altura,
          etnia: acompanhante.etnia,
          cor_olhos: acompanhante.cor_olhos,
          estilo_cabelo: acompanhante.estilo_cabelo,
          tamanho_cabelo: acompanhante.tamanho_cabelo,
          tamanho_pe: acompanhante.tamanho_pe,
          silicone: Boolean(acompanhante.silicone),
          tatuagens: Boolean(acompanhante.tatuagens),
          piercings: Boolean(acompanhante.piercings),
          fumante: acompanhante.fumante,
          idiomas: acompanhante.idiomas,
          endereco: acompanhante.endereco,
          comodidades: acompanhante.comodidades,
          bairros_atende: acompanhante.bairros_atende,
          cidades_vizinhas: acompanhante.cidades_vizinhas,
          clientes_conjunto: acompanhante.clientes_conjunto,
          atende_genero: acompanhante.atende_genero,
          horario_expediente: acompanhante.horario_expediente,
          formas_pagamento: acompanhante.formas_pagamento,
          seguidores: acompanhante.seguidores,
          favoritos: acompanhante.favoritos,
          penalidades: Boolean(acompanhante.penalidades),
          contato_seguro: Boolean(acompanhante.contato_seguro),
          data_criacao: acompanhante.data_criacao,
          foto: acompanhante.foto,
          video_verificacao: acompanhante.video_verificacao
        });
      
      if (error) {
        console.error('❌ Erro ao migrar acompanhante:', acompanhante.nome, error);
      }
    }
    console.log(`✅ ${acompanhantes.length} acompanhantes migradas`);
    
    // Migrar fotos
    console.log('📦 Migrando fotos...');
    const [fotos] = await mysqlConnection.execute('SELECT * FROM fotos');
    for (const foto of fotos) {
      const { error } = await supabase
        .from('fotos')
        .upsert({
          id: foto.id,
          acompanhante_id: foto.acompanhante_id,
          url: foto.url,
          capa: Boolean(foto.capa),
          tipo: foto.tipo
        });
      
      if (error) {
        console.error('❌ Erro ao migrar foto:', foto.id, error);
      }
    }
    console.log(`✅ ${fotos.length} fotos migradas`);
    
    // Migrar configurações
    console.log('📦 Migrando configurações...');
    const [configuracoes] = await mysqlConnection.execute('SELECT * FROM configuracoes');
    for (const config of configuracoes) {
      const { error } = await supabase
        .from('configuracoes')
        .upsert({
          id: config.id,
          chave: config.chave,
          valor: config.valor
        });
      
      if (error) {
        console.error('❌ Erro ao migrar configuração:', config.chave, error);
      }
    }
    console.log(`✅ ${configuracoes.length} configurações migradas`);
    
    // Migrar usuários
    console.log('📦 Migrando usuários...');
    const [usuarios] = await mysqlConnection.execute('SELECT * FROM usuarios');
    for (const usuario of usuarios) {
      const { error } = await supabase
        .from('usuarios')
        .upsert({
          id: usuario.id,
          email: usuario.email,
          senha: usuario.senha,
          tipo: usuario.tipo,
          acompanhante_id: usuario.acompanhante_id,
          criado_em: usuario.criado_em
        });
      
      if (error) {
        console.error('❌ Erro ao migrar usuário:', usuario.email, error);
      }
    }
    console.log(`✅ ${usuarios.length} usuários migrados`);
    
    console.log('🎉 Migração concluída com sucesso!');
    
  } catch (error) {
    console.error('❌ Erro durante a migração:', error);
  } finally {
    if (mysqlConnection) {
      await mysqlConnection.end();
      console.log('🔌 Conexão com MySQL fechada');
    }
  }
}

// Executar migração se chamado diretamente
if (require.main === module) {
  migrateData();
}

module.exports = { migrateData }; 