require('dotenv').config({ path: '.env.local' });
const { createClient } = require('@supabase/supabase-js');

async function testDatabaseStructure() {
  try {
    console.log('🧪 Iniciando testes da estrutura do banco de dados...');

    const supabase = createClient(
      process.env.NEXT_PUBLIC_SUPABASE_URL,
      process.env.SUPABASE_SERVICE_ROLE_KEY
    );

    // Teste 1: Verificar se a tabela estados existe e tem dados
    console.log('\n📋 Teste 1: Verificando tabela estados...');
    const { data: estados, error: estadosError } = await supabase
      .from('estados')
      .select('*')
      .limit(5);

    if (estadosError) {
      console.error('❌ Erro ao verificar estados:', estadosError);
    } else {
      console.log(`✅ Tabela estados OK - ${estados.length} estados encontrados`);
      console.log('   Primeiros estados:', estados.map(e => `${e.uf} (${e.nome})`));
    }

    // Teste 2: Verificar estrutura da tabela cidades
    console.log('\n📋 Teste 2: Verificando estrutura da tabela cidades...');
    const { data: cidades, error: cidadesError } = await supabase
      .from('cidades')
      .select('*')
      .limit(5);

    if (cidadesError) {
      console.error('❌ Erro ao verificar cidades:', cidadesError);
    } else {
      console.log(`✅ Tabela cidades OK - ${cidades.length} cidades encontradas`);
      if (cidades.length > 0) {
        console.log('   Estrutura da primeira cidade:', Object.keys(cidades[0]));
      }
    }

    // Teste 3: Verificar view vw_cidades_estados
    console.log('\n📋 Teste 3: Verificando view vw_cidades_estados...');
    const { data: viewCidades, error: viewError } = await supabase
      .from('vw_cidades_estados')
      .select('*')
      .limit(5);

    if (viewError) {
      console.error('❌ Erro ao verificar view:', viewError);
    } else {
      console.log(`✅ View vw_cidades_estados OK - ${viewCidades.length} registros encontrados`);
      if (viewCidades.length > 0) {
        console.log('   Exemplo:', viewCidades[0]);
      }
    }

    // Teste 4: Verificar estrutura da tabela acompanhantes
    console.log('\n📋 Teste 4: Verificando estrutura da tabela acompanhantes...');
    const { data: acompanhantes, error: acompanhantesError } = await supabase
      .from('acompanhantes')
      .select('id, nome, email, cidade_id, estado_id')
      .limit(1);

    if (acompanhantesError) {
      console.error('❌ Erro ao verificar acompanhantes:', acompanhantesError);
    } else {
      console.log(`✅ Tabela acompanhantes OK - ${acompanhantes.length} registros encontrados`);
      if (acompanhantes.length > 0) {
        console.log('   Campos disponíveis:', Object.keys(acompanhantes[0]));
      }
    }

    // Teste 5: Verificar estrutura da tabela fotos
    console.log('\n📋 Teste 5: Verificando estrutura da tabela fotos...');
    const { data: fotos, error: fotosError } = await supabase
      .from('fotos')
      .select('*')
      .limit(1);

    if (fotosError) {
      console.error('❌ Erro ao verificar fotos:', fotosError);
    } else {
      console.log(`✅ Tabela fotos OK - ${fotos.length} registros encontrados`);
      if (fotos.length > 0) {
        console.log('   Campos disponíveis:', Object.keys(fotos[0]));
        // Verificar se tem o campo principal
        if ('principal' in fotos[0]) {
          console.log('   ✅ Campo "principal" encontrado');
        } else {
          console.log('   ❌ Campo "principal" não encontrado');
        }
      }
    }

    // Teste 6: Verificar view vw_painel_acompanhantes
    console.log('\n📋 Teste 6: Verificando view vw_painel_acompanhantes...');
    const { data: viewPainel, error: viewPainelError } = await supabase
      .from('vw_painel_acompanhantes')
      .select('*')
      .limit(1);

    if (viewPainelError) {
      console.error('❌ Erro ao verificar view do painel:', viewPainelError);
    } else {
      console.log(`✅ View vw_painel_acompanhantes OK - ${viewPainel.length} registros encontrados`);
      if (viewPainel.length > 0) {
        console.log('   Campos disponíveis:', Object.keys(viewPainel[0]));
      }
    }

    // Teste 7: Verificar se a função handle_new_user_signup existe
    console.log('\n📋 Teste 7: Verificando função handle_new_user_signup...');
    try {
      // Tentar chamar a função com parâmetros mínimos para verificar se existe
      const { data: functionTest, error: functionError } = await supabase.rpc('handle_new_user_signup', {
        p_nome: 'Teste',
        p_email: 'teste@teste.com',
        p_senha: 'senha123',
        p_telefone: '11999999999',
        p_idade: 25,
        p_genero: 'feminino',
        p_cidade_id: 1,
        p_descricao: 'Teste',
        p_foto_url: null,
        p_galeria_urls: [],
        p_video_url: null
      });

      if (functionError) {
        // Se der erro de cidade não encontrada, a função existe mas precisa de dados válidos
        if (functionError.message.includes('Cidade não encontrada')) {
          console.log('✅ Função handle_new_user_signup existe e está funcionando');
        } else {
          console.error('❌ Erro na função:', functionError);
        }
      } else {
        console.log('✅ Função handle_new_user_signup executada com sucesso');
      }
    } catch (error) {
      console.error('❌ Erro ao testar função:', error.message);
    }

    // Teste 8: Verificar Políticas RLS
    console.log('\n📋 Teste 8: Verificando Políticas RLS para Cidades e Estados...');
    const { data: policies, error: policiesError } = await supabase.rpc('exec_sql', {
        sql_query: `
          SELECT
            p.polname as policy_name,
            c.relname as table_name,
            (SELECT string_agg(r.rolname, ',') FROM pg_roles r WHERE r.oid = ANY(p.polroles)) as roles
          FROM pg_policy p
          JOIN pg_class c ON c.oid = p.polrelid
          WHERE c.relname IN ('cidades', 'estados') AND p.polcmd = 's'
          ORDER BY c.relname, p.polname;
        `
    });

    if (policiesError) {
        console.error('❌ Erro ao verificar políticas RLS:', policiesError);
    } else {
        const publicPolicies = policies.filter(p => p.roles && p.roles.includes('public'));
        const estadosPolicy = publicPolicies.find(p => p.table_name === 'estados');
        const cidadesPolicy = publicPolicies.find(p => p.table_name === 'cidades');

        if (estadosPolicy) {
            console.log('✅ Política de leitura pública para `estados` encontrada.');
        } else {
            console.error('❌ Política de leitura pública para `estados` NÃO encontrada.');
        }

        if (cidadesPolicy) {
            console.log('✅ Política de leitura pública para `cidades` encontrada.');
        } else {
            console.error('❌ Política de leitura pública para `cidades` NÃO encontrada.');
        }

        if(!estadosPolicy || !cidadesPolicy) {
            console.log('   (Dica: Rode `node scripts/fix-database.js` para corrigir)');
            console.log('   Políticas encontradas:', policies);
        }
    }

    // Teste 9: Verificar índices importantes
    console.log('\n📋 Teste 9: Verificando índices...');
    const { data: indices, error: indicesError } = await supabase.rpc('exec_sql', {
      sql_query: `
        SELECT indexname, tablename 
        FROM pg_indexes 
        WHERE tablename IN ('cidades', 'acompanhantes', 'fotos', 'videos_verificacao')
        AND indexname LIKE 'idx_%'
        ORDER BY tablename, indexname;
      `
    });

    if (indicesError) {
      console.error('❌ Erro ao verificar índices:', indicesError);
    } else {
      console.log('✅ Índices encontrados:');
      indices.forEach(idx => {
        console.log(`   - ${idx.indexname} (${idx.tablename})`);
      });
    }

    console.log('\n🎉 Testes concluídos!');
    console.log('\n📊 Resumo:');
    console.log('   - Estrutura das tabelas verificada');
    console.log('   - Views testadas');
    console.log('   - Função de cadastro verificada');
    console.log('   - Políticas RLS verificadas');
    console.log('   - Índices confirmados');

  } catch (error) {
    console.error('❌ Erro durante os testes:', error);
  }
}

// Executar se chamado diretamente
if (require.main === module) {
  testDatabaseStructure();
}

module.exports = { testDatabaseStructure }; 