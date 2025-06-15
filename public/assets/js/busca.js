document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('filtroBusca');
    const resultados = document.getElementById('resultados');
    const loading = document.getElementById('loadingResults');
    const noResults = document.querySelector('.no-results');
    let favoritos = JSON.parse(localStorage.getItem('favoritos') || '[]');

    // Preencher cidades, tags e serviços
    Promise.all([
        fetch('api/buscar.php?cidades=1').then(r => r.json()),
        fetch('api/buscar.php?tags=1').then(r => r.json()),
        fetch('api/buscar.php?servicos=1').then(r => r.json())
    ]).then(([cidades, tags, servicos]) => {
        const selCidade = document.getElementById('cidadeFiltro');
        const selTag = document.getElementById('tagFiltro');
        const selServicos = document.getElementById('servicosFiltro');
        
        cidades.forEach(c => {
            let opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.nome;
            selCidade.appendChild(opt);
        });
        
        tags.forEach(t => {
            let opt = document.createElement('option');
            opt.value = t.id;
            opt.textContent = t.nome;
            selTag.appendChild(opt);
        });
        
        servicos.forEach(s => {
            let opt = document.createElement('option');
            opt.value = s.id;
            opt.textContent = s.nome;
            selServicos.appendChild(opt);
        });
    });

    // Submeter busca
    form.onsubmit = async function(e) {
        e.preventDefault();
        loading.style.display = 'block';
        noResults.style.display = 'none';
        resultados.innerHTML = '';
        resultados.classList.add('fade');
        let erroMsg = document.getElementById('erroBusca');
        if (erroMsg) erroMsg.remove();
        const dados = new FormData(this);
        const servicosSel = Array.from(document.getElementById('servicosFiltro').selectedOptions).map(opt => opt.value);
        servicosSel.forEach(sid => dados.append('servicos[]', sid));
        const verificado = document.getElementById('verificadoFiltro').checked;
        if (verificado) dados.set('verificado', '1'); else dados.delete('verificado');
        let params = new URLSearchParams(dados).toString();
        
        try {
            const res = await fetch('api/buscar.php?' + params);
            if (!res.ok) throw new Error('Erro na requisição: ' + res.status);
            const acompanhantes = await res.json();
            resultados.classList.remove('fade');
            if (acompanhantes.length === 0) {
                noResults.style.display = 'block';
                return;
            }

            // Ordenar resultados
            const ordem = dados.get('ordem');
            if (ordem) {
                acompanhantes.sort((a, b) => {
                    switch (ordem) {
                        case 'valor_asc':
                            return (a.valor || 0) - (b.valor || 0);
                        case 'valor_desc':
                            return (b.valor || 0) - (a.valor || 0);
                        case 'avaliacao':
                            return (b.avaliacao || 0) - (a.avaliacao || 0);
                        default:
                            return a.nome.localeCompare(b.nome);
                    }
                });
            }

            // Renderizar cards
            acompanhantes.forEach(a => {
                const card = document.createElement('div');
                card.className = 'card';
                // Montar diferenciais
                let diferenciais = [];
                if (a.aceita_cartao) diferenciais.push('Cartão');
                if (a.atende_casal) diferenciais.push('Casal');
                if (a.local_proprio) diferenciais.push('Local próprio');
                if (a.aceita_pix) diferenciais.push('PIX');
                card.innerHTML = `
                    <a href="/perfil.php?id=${a.id}" class="card-img">
                        <img src="${a.foto || 'assets/img/placeholder.png'}" alt="${a.nome}" loading="lazy">
                    </a>
                    <div class="card-info">
                        <h3>${a.nome}</h3>
                        <div class="card-meta">
                            <span><img src="${window.ICONS.map_marker}" alt="Localização" style="width: 16px; height: 16px;">${a.cidade || ''}</span>
                            <span><img src="${window.ICONS.user}" alt="Idade" style="width: 16px; height: 16px;">${a.idade ? a.idade + ' anos' : ''}</span>
                            ${a.avaliacao ? `<span><img src="${window.ICONS.star}" alt="Avaliação" style="width: 16px; height: 16px;">${a.avaliacao.toFixed(1)}</span>` : ''}
                        </div>
                        ${a.valor ? `<div class="card-valor">R$ ${a.valor}</div>` : ''}
                        ${a.tags && a.tags.length ? `
                            <div class="card-tags">
                                ${a.tags.map(t => `<span class="card-tag">${t}</span>`).join('')}
                            </div>
                        ` : ''}
                        ${diferenciais.length ? `<div class="card-tags card-diferenciais">${diferenciais.map(d => `<span class=\"card-tag card-dif\">${d}</span>`).join('')}</div>` : ''}
                        <div class="card-actions">
                            <button class="btn-favorito ${favoritos.includes(a.id) ? 'ativo' : ''}" 
                                    onclick="toggleFavorito(${a.id}, this)">
                                <img src="${window.ICONS.heart}" alt="Favorito">
                                Favorito
                            </button>
                            <a href="/contato.php?id=${a.id}" class="btn-contato">
                                <img src="${window.ICONS.whatsapp}" alt="WhatsApp" style="width: 20px; height: 20px;">
                                Contato
                            </a>
                        </div>
                    </div>
                `;
                resultados.appendChild(card);
            });
        } catch (err) {
            resultados.classList.remove('fade');
            loading.style.display = 'none';
            let erro = document.createElement('div');
            erro.id = 'erroBusca';
            erro.style.background = '#ffe0e0';
            erro.style.color = '#b00';
            erro.style.padding = '14px 18px';
            erro.style.borderRadius = '8px';
            erro.style.margin = '18px auto';
            erro.style.maxWidth = '400px';
            erro.style.textAlign = 'center';
            erro.style.fontWeight = '600';
            erro.innerText = 'Erro ao buscar acompanhantes. Tente novamente.';
            resultados.parentNode.insertBefore(erro, resultados);
            console.error('Erro na busca:', err);
        } finally {
            loading.style.display = 'none';
        }
    };

    // Limpar filtros
    form.querySelector('.btn-limpar').onclick = function(e) {
        e.preventDefault();
        form.reset();
        let erroMsg = document.getElementById('erroBusca');
        if (erroMsg) erroMsg.remove();
        noResults.style.display = 'none';
        loading.style.display = 'block';
        resultados.innerHTML = '';
        resultados.classList.add('fade');
        form.dispatchEvent(new Event('submit'));
    };

    // Substituir ícone de limpar
    form.querySelector('.btn-limpar').innerHTML = `<img src="${window.ICONS.refresh}" alt="Limpar"> Limpar`;

    // Validação de idade
    const idadeMin = document.getElementById('idadeMin');
    const idadeMax = document.getElementById('idadeMax');
    
    function validarIdade() {
        const min = parseInt(idadeMin.value) || 0;
        const max = parseInt(idadeMax.value) || 0;
        if (min > max && max > 0) {
            idadeMax.value = min;
        }
    }
    
    idadeMin.onchange = validarIdade;
    idadeMax.onchange = validarIdade;

    // Adicionar feedback ao clicar em contato
    resultados.addEventListener('click', function(e) {
        if (e.target.closest('.btn-contato')) {
            e.preventDefault();
            const link = e.target.closest('.btn-contato').getAttribute('href');
            showMsg('Redirecionando para o WhatsApp...');
            setTimeout(() => { window.location.href = link; }, 700);
        }
    });

    // Função de mensagem flutuante
    function showMsg(msg) {
        let el = document.createElement('div');
        el.textContent = msg;
        el.style.position = 'fixed';el.style.top='20px';el.style.left='50%';el.style.transform='translateX(-50%)';el.style.background='#C5A572';el.style.color='#fff';el.style.padding='10px 24px';el.style.borderRadius='8px';el.style.zIndex=9999;el.style.fontWeight='600';el.style.boxShadow='0 2px 8px #0002';
        document.body.appendChild(el);
        setTimeout(()=>{el.remove();},1800);
    }
});

// Função global para favoritos
function toggleFavorito(id, btn) {
    let favoritos = JSON.parse(localStorage.getItem('favoritos') || '[]');
    const index = favoritos.indexOf(id);
    
    if (index === -1) {
        favoritos.push(id);
        btn.classList.add('ativo');
    } else {
        favoritos.splice(index, 1);
        btn.classList.remove('ativo');
    }
    
    localStorage.setItem('favoritos', JSON.stringify(favoritos));
} 