// ===== FUNÇÕES GERAIS =====

// Função para mostrar/esconder loading
function showLoading() {
    const loading = document.getElementById('loading');
    if (loading) {
        loading.classList.remove('d-none');
    }
}

function hideLoading() {
    const loading = document.getElementById('loading');
    if (loading) {
        loading.classList.add('d-none');
    }
}

// Função para mostrar notificações
function showNotification(message, type = 'success') {
    const alertClass = `alert-${type}`;
    const iconClass = type === 'success' ? 'fas fa-check-circle' : 
                     type === 'error' ? 'fas fa-exclamation-circle' : 
                     type === 'warning' ? 'fas fa-exclamation-triangle' : 
                     'fas fa-info-circle';
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="${iconClass}"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', alertHtml);
    
    // Auto-remover após 5 segundos
    setTimeout(() => {
        const alert = document.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}

// Função para confirmar ações
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Função para formatar números
function formatNumber(num) {
    return new Intl.NumberFormat('pt-BR').format(num);
}

// Função para formatar moeda
function formatCurrency(value) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value);
}

// Função para formatar data
function formatDate(date) {
    return new Intl.DateTimeFormat('pt-BR').format(new Date(date));
}

// ===== FUNÇÕES DE FORMULÁRIO =====

// Validação de formulários
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

// Limpar formulário
function clearForm(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.reset();
        form.querySelectorAll('.is-invalid').forEach(input => {
            input.classList.remove('is-invalid');
        });
    }
}

// ===== FUNÇÕES DE UPLOAD =====

// Preview de imagem
function previewImage(input, previewId) {
    const file = input.files[0];
    const preview = document.getElementById(previewId);
    
    if (file && preview) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}

// Upload de arquivo com progresso
function uploadFile(file, url, onProgress, onSuccess, onError) {
    const xhr = new XMLHttpRequest();
    const formData = new FormData();
    
    formData.append('file', file);
    
    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            const percentComplete = (e.loaded / e.total) * 100;
            onProgress(percentComplete);
        }
    });
    
    xhr.addEventListener('load', function() {
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                onSuccess(response);
            } catch (e) {
                onError('Erro ao processar resposta');
            }
        } else {
            onError('Erro no upload');
        }
    });
    
    xhr.addEventListener('error', function() {
        onError('Erro de conexão');
    });
    
    xhr.open('POST', url);
    xhr.send(formData);
}

// ===== FUNÇÕES DE TABELA =====

// Ordenar tabela
function sortTable(tableId, columnIndex) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        const aValue = a.cells[columnIndex].textContent.trim();
        const bValue = b.cells[columnIndex].textContent.trim();
        
        // Tentar converter para número
        const aNum = parseFloat(aValue.replace(/[^\d.-]/g, ''));
        const bNum = parseFloat(bValue.replace(/[^\d.-]/g, ''));
        
        if (!isNaN(aNum) && !isNaN(bNum)) {
            return aNum - bNum;
        }
        
        return aValue.localeCompare(bValue, 'pt-BR');
    });
    
    // Remover linhas existentes
    rows.forEach(row => row.remove());
    
    // Adicionar linhas ordenadas
    rows.forEach(row => tbody.appendChild(row));
}

// Filtrar tabela
function filterTable(tableId, searchTerm) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const match = text.includes(searchTerm.toLowerCase());
        row.style.display = match ? '' : 'none';
    });
}

// ===== FUNÇÕES DE PAGINAÇÃO =====

// Paginação simples
function paginate(items, itemsPerPage, currentPage) {
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    return items.slice(startIndex, endIndex);
}

// Criar controles de paginação
function createPagination(totalPages, currentPage, onPageChange) {
    const pagination = document.createElement('nav');
    pagination.innerHTML = `
        <ul class="pagination justify-content-center">
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="onPageChange(${currentPage - 1})">
                    <i class="fas fa-chevron-left"></i> Anterior
                </a>
            </li>
    `;
    
    for (let i = 1; i <= totalPages; i++) {
        pagination.innerHTML += `
            <li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" onclick="onPageChange(${i})">${i}</a>
            </li>
        `;
    }
    
    pagination.innerHTML += `
            <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="onPageChange(${currentPage + 1})">
                    Próxima <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        </ul>
    `;
    
    return pagination;
}

// ===== FUNÇÕES DE GRÁFICOS =====

// Criar gráfico de barras simples
function createBarChart(canvasId, data, options = {}) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    const defaultOptions = {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    };
    
    new Chart(ctx, {
        type: 'bar',
        data: data,
        options: { ...defaultOptions, ...options }
    });
}

// Criar gráfico de pizza
function createPieChart(canvasId, data, options = {}) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    const defaultOptions = {
        responsive: true,
        maintainAspectRatio: false
    };
    
    new Chart(ctx, {
        type: 'pie',
        data: data,
        options: { ...defaultOptions, ...options }
    });
}

// ===== FUNÇÕES DE LOCALIZAÇÃO =====

// Máscara para telefone
function maskPhone(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length <= 11) {
        value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
        value = value.replace(/(\d)(\d{4})$/, '$1-$2');
        input.value = value;
    }
}

// Máscara para CPF
function maskCPF(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length <= 11) {
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        input.value = value;
    }
}

// Máscara para CEP
function maskCEP(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length <= 8) {
        value = value.replace(/^(\d{5})(\d)/, '$1-$2');
        input.value = value;
    }
}

// ===== FUNÇÕES DE BUSCA =====

// Busca com debounce
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Busca em tempo real
function setupLiveSearch(searchInputId, targetContainerId, searchFunction) {
    const searchInput = document.getElementById(searchInputId);
    const targetContainer = document.getElementById(targetContainerId);
    
    if (!searchInput || !targetContainer) return;
    
    const debouncedSearch = debounce(function(searchTerm) {
        searchFunction(searchTerm, targetContainer);
    }, 300);
    
    searchInput.addEventListener('input', function() {
        debouncedSearch(this.value);
    });
}

// ===== FUNÇÕES DE MODAL =====

// Abrir modal
function openModal(modalId) {
    const modal = new bootstrap.Modal(document.getElementById(modalId));
    modal.show();
}

// Fechar modal
function closeModal(modalId) {
    const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
    if (modal) {
        modal.hide();
    }
}

// ===== FUNÇÕES DE COOKIE =====

// Definir cookie
function setCookie(name, value, days) {
    const expires = new Date();
    expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
    document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/`;
}

// Obter cookie
function getCookie(name) {
    const nameEQ = name + "=";
    const ca = document.cookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

// Remover cookie
function removeCookie(name) {
    document.cookie = `${name}=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;`;
}

// ===== FUNÇÕES DE AJAX =====

// Requisição AJAX simples
function ajaxRequest(url, method = 'GET', data = null, headers = {}) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        
        xhr.open(method, url);
        
        // Definir headers
        Object.keys(headers).forEach(key => {
            xhr.setRequestHeader(key, headers[key]);
        });
        
        xhr.addEventListener('load', function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    resolve(response);
                } catch (e) {
                    resolve(xhr.responseText);
                }
            } else {
                reject(new Error(`HTTP ${xhr.status}: ${xhr.statusText}`));
            }
        });
        
        xhr.addEventListener('error', function() {
            reject(new Error('Erro de rede'));
        });
        
        if (data) {
            xhr.send(JSON.stringify(data));
        } else {
            xhr.send();
        }
    });
}

// ===== INICIALIZAÇÃO =====

document.addEventListener('DOMContentLoaded', function() {
    // Aplicar máscaras
    const phoneInputs = document.querySelectorAll('input[type="tel"], input[name*="telefone"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function() {
            maskPhone(this);
        });
    });
    
    const cpfInputs = document.querySelectorAll('input[name*="cpf"]');
    cpfInputs.forEach(input => {
        input.addEventListener('input', function() {
            maskCPF(this);
        });
    });
    
    const cepInputs = document.querySelectorAll('input[name*="cep"]');
    cepInputs.forEach(input => {
        input.addEventListener('input', function() {
            maskCEP(this);
        });
    });
    
    // Auto-hide alerts
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
    
    // Smooth scroll para links internos
    const internalLinks = document.querySelectorAll('a[href^="#"]');
    internalLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href === '#') return;
            e.preventDefault();
            const target = document.querySelector(href);
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Tooltip initialization
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Popover initialization
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});

// ===== EXPORTAÇÃO DE FUNÇÕES =====

// Tornar funções disponíveis globalmente
window.showLoading = showLoading;
window.hideLoading = hideLoading;
window.showNotification = showNotification;
window.confirmAction = confirmAction;
window.formatNumber = formatNumber;
window.formatCurrency = formatCurrency;
window.formatDate = formatDate;
window.validateForm = validateForm;
window.clearForm = clearForm;
window.previewImage = previewImage;
window.uploadFile = uploadFile;
window.sortTable = sortTable;
window.filterTable = filterTable;
window.paginate = paginate;
window.createPagination = createPagination;
window.createBarChart = createBarChart;
window.createPieChart = createPieChart;
window.maskPhone = maskPhone;
window.maskCPF = maskCPF;
window.maskCEP = maskCEP;
window.debounce = debounce;
window.setupLiveSearch = setupLiveSearch;
window.openModal = openModal;
window.closeModal = closeModal;
window.setCookie = setCookie;
window.getCookie = getCookie;
window.removeCookie = removeCookie;
window.ajaxRequest = ajaxRequest; 