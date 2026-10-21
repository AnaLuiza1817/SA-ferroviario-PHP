
(() => {
    'use strict';
    const $ = id => document.getElementById(id);
    const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const classe = v => String(v || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '-');
    const num = v => Number(v) || 0;

    document.addEventListener('DOMContentLoaded', () => {
        const links = document.querySelectorAll('a[href$="logout.php"]');
        if (links.length) {
            const modal = document.createElement('div');
            modal.className = 'modal';
            modal.tabIndex = -1;
            modal.innerHTML = '<div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Sair da conta</h5><button class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button></div><div class="modal-body">Deseja realmente sair da sua conta?</div><div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><a class="btn btn-danger" id="confirmarLogout" href="logout.php">Sair da conta</a></div></div></div>';
            document.body.appendChild(modal);
            links.forEach(a => a.addEventListener('click', e => {
                e.preventDefault();
                $('confirmarLogout').href = a.href;
                if (window.bootstrap?.Modal) bootstrap.Modal.getOrCreateInstance(modal).show();
                else if (confirm('Deseja realmente sair da sua conta?')) location.href = a.href;
            }));
        }
        document.querySelectorAll('form[data-validar="usuario"]').forEach(form => form.addEventListener('submit', e => {
            if (!form.checkValidity()) e.preventDefault();
            form.classList.add('was-validated');
        }));
        document.querySelectorAll('input[name="telefone"]').forEach(input => input.addEventListener('input', () => {
            const n = input.value.replace(/\D/g, '').slice(0, 11);
            input.value = n.length > 10 ? n.replace(/^(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3') : n.replace(/^(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
        }));
        if ($('tabelaUsuarios') && window.jQuery?.fn?.DataTable && !jQuery.fn.DataTable.isDataTable('#tabelaUsuarios')) {
            jQuery('#tabelaUsuarios').DataTable({pageLength:5, language:{search:'Pesquisar:', lengthMenu:'Mostrar _MENU_ usuários', info:'Mostrando _START_ a _END_ de _TOTAL_ usuários', infoEmpty:'Nenhum usuário encontrado', zeroRecords:'Nenhum usuário encontrado', paginate:{previous:'Anterior',next:'Próximo'}}});
        }
        graficos();
        mapa();
    });

    function graficos() {
        if (!$('graficoManutencoes') || !$('graficoOcorrencias') || !window.Chart) return;
        const dados = window.dadosManutencoes || {};
        const anos = (window.anosGrafico || Object.keys(dados)).map(String);
        const ocorrencias = window.dadosOcorrencias || [];
        const detalhes = window.detalhesTrens || {};
        const linha = new Chart($('graficoManutencoes'), {
            type:'line', data:{labels:window.mesesGrafico || [], datasets:anos.map((ano,i) => ({label:ano,data:dados[ano] || Array(12).fill(0),borderWidth:2,tension:0,pointRadius:3,hidden:i !== anos.length-1}))},
            options:{animation:false,responsive:true,maintainAspectRatio:false,scales:{y:{beginAtZero:true,ticks:{precision:0}}}}
        });
        document.querySelectorAll('.grafico-toggle').forEach(b => {
            b.classList.toggle('active', b.dataset.ano === anos[anos.length-1]);
            b.addEventListener('click', () => {
                linha.data.datasets.forEach(d => d.hidden = d.label !== b.dataset.ano);
                document.querySelectorAll('.grafico-toggle').forEach(x => x.classList.toggle('active', x === b));
                linha.update('none');
            });
        });
        function mostrar(codigo) {
            const t = detalhes[codigo];
            if (!t || !$('detalhesTrem')) return;
            const lista = (itens, formato) => itens?.length ? '<ul>' + itens.slice(0,4).map(x => '<li>' + formato(x) + '</li>').join('') + '</ul>' : '<p>Nenhum registro.</p>';
            $('detalhesTrem').innerHTML = '<div class="detail-heading"><strong>' + esc(t.codigo) + '</strong><span class="status-badge">' + esc(t.status) + '</span></div>' +
                '<p><strong>Rota:</strong> ' + esc(t.rota) + '</p><p><strong>Ocorrências:</strong> ' + num(t.total_ocorrencias) + '</p>' +
                '<h6>Tipos de ocorrência</h6>' + lista(t.tipos, x => esc(x.tipo) + ': ' + num(x.total)) +
                '<h6>Ocorrências recentes</h6>' + lista(t.ocorrencias, x => esc(x.tipo) + ': ' + esc(x.descricao)) +
                '<h6>Manutenções</h6>' + lista(t.manutencoes, x => esc(x.componente) + ': ' + esc(x.status));
        }
        new Chart($('graficoOcorrencias'), {
            type:'bar', data:{labels:ocorrencias.map(t => t.codigo),datasets:[{label:'Ocorrências',data:ocorrencias.map(t => num(t.total_ocorrencias))}]},
            options:{animation:false,responsive:true,maintainAspectRatio:false,scales:{y:{beginAtZero:true,ticks:{precision:0}}},onClick:(_e,barras) => {
                if (barras.length) mostrar(ocorrencias[barras[0].index].codigo);
            }}
        });
        if (ocorrencias.length) mostrar(ocorrencias[0].codigo);
    }

    function mapa() {
        const svg = $('mapa-ferroviario'), dados = window.dadosMapa;
        if (!svg || !dados) return;
        const itens = k => Array.isArray(dados[k]) ? dados[k] : [];
        const camada = k => $('camada' + k);
        const estado = {x:0,y:0,w:1040,h:430,pan:null,mov:null};
        const rotasAtivas = {};
        itens('trens').forEach(t => rotasAtivas[t.id] = num(t.rota_ativa_id || t.rota_id));
        const trecho = id => itens('trechos').find(t => num(t.id) === num(id));
        const nomeRota = id => itens('rotas').find(r => num(r.id) === num(id))?.codigo || 'Sem rota';
        const ponto = (t,p=50) => t ? {x:num(t.origem_x)+(num(t.destino_x)-num(t.origem_x))*p/100,y:num(t.origem_y)+(num(t.destino_y)-num(t.origem_y))*p/100} : null;
        function elemento(tag,attrs,texto) {
            const el = document.createElementNS('http://www.w3.org/2000/svg',tag);
            Object.entries(attrs).forEach(([k,v]) => el.setAttribute(k,v));
            if (texto !== undefined) el.textContent = texto;
            return el;
        }
        function adicionar(pai,tag,attrs,item,tipo,texto) {
            if (!pai) return;
            const el = elemento(tag,attrs,texto);
            if (tipo) el.addEventListener('click', e => {e.stopPropagation(); selecionar(tipo,item);});
            pai.appendChild(el);
            return el;
        }
        function campos(tipo,t) {
            if (tipo === 'trem') return [['Trecho',t.trecho_codigo],['Rota ativa',nomeRota(rotasAtivas[t.id])],['Ocorrência',t.ocorrencia_descricao],['Manutenção',t.manutencao_componente]];
            if (tipo === 'trecho') return [['Origem',t.origem_nome],['Destino',t.destino_nome],['Distância',t.distancia_km+' km']];
            if (tipo === 'estacao') return [['Nome',t.nome],['Ordem',t.ordem]];
            if (tipo === 'sensor') return [['Tipo',t.tipo],['Leitura',t.leitura+' '+(t.unidade || '')],['Limite',t.limite]];
            if (tipo === 'amv') return [['Estado',t.estado],['Trecho',t.trecho_codigo]];
            if (tipo === 'ocorrencia') return [['Trem',t.trem_codigo],['Trecho',t.trecho_codigo],['Descrição',t.descricao]];
            if (tipo === 'manutencao') return [['Trem',t.trem_codigo],['Componente',t.componente],['Status',t.status]];
            if (tipo === 'alerta') return [['Mensagem',t.mensagem],['Sensor',t.sensor_codigo],['Trem',t.trem_codigo]];
            return [['Rota',t.codigo]];
        }
        function selecionar(tipo,item) {
            if (!$('painelTipo') || !$('painelOperacional')) return;
            $('painelTipo').textContent = tipo[0].toUpperCase()+tipo.slice(1);
            let html = '<div class="detail-heading"><strong>'+esc(item.codigo || item.nome || item.tipo || tipo)+'</strong><span class="status-badge">'+esc(item.status || '')+'</span></div>';
            campos(tipo,item).forEach(([nome,valor]) => html += '<p><strong>'+esc(nome)+':</strong> '+esc(valor ?? 'Não informado')+'</p>');
            if (tipo === 'trem') html += '<button class="btn btn-primary btn-sm w-100" id="simularAlteracaoRota">Simular alteração de rota</button>';
            $('painelOperacional').innerHTML = html;
            if (tipo === 'trem') $('simularAlteracaoRota').onclick = () => simular(item);
        }
        function simular(t) {
            const atual = rotasAtivas[t.id], nova = itens('rotas').map(r => num(r.id)).find(id => id && id !== atual);
            if (!nova) return alert('Não existe outra rota disponível.');
            rotasAtivas[t.id] = nova;
            desenharRotas();
            selecionar('trem',t);
            $('painelOperacional')?.insertAdjacentHTML('beforeend','<p class="route-change mt-3">Simulação: '+esc(nomeRota(atual))+' → '+esc(nomeRota(nova))+'. Não altera o banco de dados.</p>');
        }
        function desenharRotas() {
            const pai = camada('Rotas');
            if (!pai) return;
            pai.replaceChildren();
            const ids = new Set(Object.values(rotasAtivas));
            itens('rotas').filter(r => ids.has(num(r.id)) && r.trecho_id).forEach(r => {
                const t = trecho(r.trecho_id);
                if (t) adicionar(pai,'line',{x1:t.origem_x,y1:t.origem_y,x2:t.destino_x,y2:t.destino_y,class:'rota-ativa'},r,'rota');
            });
        }
        itens('trechos').forEach(t => {
            adicionar(camada('Trechos'),'line',{x1:t.origem_x,y1:t.origem_y,x2:t.destino_x,y2:t.destino_y,class:'trecho-linha status-'+classe(t.status)},t,'trecho');
            const p = ponto(t);
            adicionar(camada('Trechos'),'text',{x:p.x,y:p.y-10,class:'trecho-rotulo'},null,null,t.codigo);
        });
        desenharRotas();
        itens('estacoes').forEach(e => {
            adicionar(camada('Estacoes'),'circle',{cx:e.posicao_x,cy:e.posicao_y,r:11,class:'estacao-ponto'},e,'estacao');
            adicionar(camada('Estacoes'),'text',{x:e.posicao_x,y:num(e.posicao_y)+30,class:'estacao-rotulo','text-anchor':'middle'},null,null,e.nome);
        });
        function marcadores(tipo,dx,dy,tag,css,nomeCamada) {
            itens(tipo === 'manutencao' ? 'manutencoes' : tipo+'s').forEach((item,i) => {
                const p = ponto(trecho(item.trecho_id));
                if (!p) return;
                const x=p.x+dx, y=p.y+dy+(i%2)*8;
                const attrs = tag === 'rect' ? {x:x-6,y:y-6,width:12,height:12} : {cx:x,cy:y,r:7};
                attrs.class = css + (tipo === 'sensor' ? ' status-'+classe(item.status) : tipo === 'alerta' ? ' nivel-'+classe(item.nivel) : '');
                adicionar(camada(nomeCamada) || (tipo === 'alerta' ? camada('Manutencoes') : null),tag,attrs,item,tipo);
            });
        }
        marcadores('sensor',12,-18,'circle','sensor-ponto','Sensores');
        marcadores('amv',0,0,'rect','amv-ponto','Amvs');
        marcadores('ocorrencia',-15,-20,'circle','ocorrencia-ponto','Ocorrencias');
        marcadores('manutencao',18,8,'rect','manutencao-ponto','Manutencoes');
        marcadores('alerta',0,24,'circle','alerta-ponto','Alertas');

        const podeMover = !!window.podeControlarTrens;
        itens('trens').forEach(t => {
            const p = ponto(trecho(t.trecho_id),num(t.posicao_percentual));
            if (!p) return;
            const g=elemento('g',{class:'trem-grupo'+(podeMover?' trem-movivel':'')});
            const bola=elemento('circle',{cx:p.x,cy:p.y,r:10,class:'trem-ponto status-trem-'+classe(t.status)});
            const texto=elemento('text',{x:p.x,y:p.y-16,class:'trem-rotulo','text-anchor':'middle'},t.codigo);
            g.append(bola,texto);
            g.addEventListener('click',e => {e.stopPropagation();selecionar('trem',t);});
            if (podeMover) bola.addEventListener('pointerdown',e => {
                if (e.button !== 0) return;
                e.preventDefault(); e.stopPropagation();
                estado.mov={t,g,bola,texto,origem:p,destino:null};
                g.classList.add('arrastando');
            });
            camada('Trens')?.appendChild(g);
        });
        function coordenada(e) {
            const matriz=svg.getScreenCTM();
            if (!matriz) return null;
            const p=svg.createSVGPoint(); p.x=e.clientX;p.y=e.clientY;
            return p.matrixTransform(matriz.inverse());
        }
        function proximo(p) {
            if (!p) return null;
            let melhor=null;
            itens('trechos').forEach(t => {
                const a={x:num(t.origem_x),y:num(t.origem_y)},b={x:num(t.destino_x),y:num(t.destino_y)};
                const dx=b.x-a.x,dy=b.y-a.y,tam=dx*dx+dy*dy;
                if (!tam) return;
                const f=Math.max(0,Math.min(1,((p.x-a.x)*dx+(p.y-a.y)*dy)/tam));
                const x=a.x+f*dx,y=a.y+f*dy,distancia=Math.hypot(p.x-x,p.y-y);
                if (!melhor || distancia<melhor.distancia) melhor={t,x,y,percentual:f*100,distancia};
            });
            return melhor && melhor.distancia<=45 ? melhor : null;
        }
        function zoomAtual() { svg.setAttribute('viewBox',[estado.x,estado.y,estado.w,estado.h].join(' ')); }
        svg.addEventListener('pointerdown',e => {
            if (estado.mov || e.button !== 0) return;
            estado.pan={x:e.clientX,y:e.clientY};
            $('mapaViewport')?.classList.add('arrastando');
        });
        svg.addEventListener('pointermove',e => {
            if (estado.mov) {
                const perto=proximo(coordenada(e));
                estado.mov.destino=perto;
                if (perto) {
                    estado.mov.bola.setAttribute('cx',perto.x);
                    estado.mov.bola.setAttribute('cy',perto.y);
                    estado.mov.texto.setAttribute('x',perto.x);
                    estado.mov.texto.setAttribute('y',perto.y-16);
                }
            } else if (estado.pan) {
                const rect=svg.getBoundingClientRect();
                if (!rect.width || !rect.height) return;
                estado.x-=(e.clientX-estado.pan.x)*estado.w/rect.width;
                estado.y-=(e.clientY-estado.pan.y)*estado.h/rect.height;
                estado.pan={x:e.clientX,y:e.clientY};
                zoomAtual();
            }
        });
        window.addEventListener('pointerup',async () => {
            if (estado.pan) {estado.pan=null;$('mapaViewport')?.classList.remove('arrastando');}
            const m=estado.mov;
            if (!m) return;
            estado.mov=null;
            m.g.classList.remove('arrastando');
            const voltar=() => {
                m.bola.setAttribute('cx',m.origem.x);
                m.bola.setAttribute('cy',m.origem.y);
                m.texto.setAttribute('x',m.origem.x);
                m.texto.setAttribute('y',m.origem.y-16);
            };
            if (!m.destino) return voltar();
            const corpo=new URLSearchParams({id:m.t.id,trecho_id:m.destino.t.id,posicao_percentual:m.destino.percentual.toFixed(2),csrf_token:window.csrfToken || ''});
            try {
                const resposta=await fetch(window.urlAtualizarTrem || 'atualizar.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},credentials:'same-origin',body:corpo.toString()});
                const resultado=await resposta.json();
                if (!resposta.ok || !resultado.ok) throw Error(resultado.erro || 'Falha ao salvar.');
                m.t.trecho_id=m.destino.t.id;
                m.t.trecho_codigo=m.destino.t.codigo;
                m.t.posicao_percentual=m.destino.percentual;
                selecionar('trem',m.t);
            } catch (e) {voltar();alert('Não foi possível mover o trem: '+e.message);}
        });
        function zoom(fator) {
            const w=Math.max(250,Math.min(1040,estado.w*fator)),h=Math.max(105,Math.min(430,estado.h*fator));
            estado.x+=(estado.w-w)/2;estado.y+=(estado.h-h)/2;
            estado.w=w;estado.h=h;
            zoomAtual();
        }
        $('mapaZoomMais')?.addEventListener('click',() => zoom(.8));
        $('mapaZoomMenos')?.addEventListener('click',() => zoom(1.25));
        $('mapaReset')?.addEventListener('click',() => {Object.assign(estado,{x:0,y:0,w:1040,h:430});zoomAtual();});
        svg.addEventListener('wheel',e => {e.preventDefault();zoom(e.deltaY>0?1.12:.89);},{passive:false});
    }
})();
