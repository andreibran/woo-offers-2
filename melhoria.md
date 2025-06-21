# 🔄 Refatoração Completa: Woo Offers v3.0

## 📋 Visão Geral da Refatoração

Esta refatoração visa transformar o Woo Offers em uma solução robusta de campanhas de upsell/cross-sell, seguindo as melhores práticas observadas no FunnelKit, com foco em:

- **Arquitetura moderna** e escalável
- **Fluxo de campanhas** intuitivo
- **Segurança** aprimorada
- **Performance** otimizada
- **UX/UI** profissional

---

## 🎯 Novo Fluxo de Campanhas (Inspirado no FunnelKit)

### **Estrutura de Navegação**
```
Campanhas
├── Dashboard                    # Visão geral de todas as campanhas
├── Todas as Campanhas          # Lista/gerenciamento de campanhas  
├── Adicionar Nova Campanha     # Wizard de criação
├── Analytics                   # Relatórios e métricas
├── A/B Tests                   # Testes comparativos
├── Templates                   # Modelos pré-construídos
└── Configurações              # Settings globais
```

### **Wizard de Criação de Campanha**
```
Passo 1: Tipo de Campanha
├── Checkout Upsells           # Ofertas durante o checkout
├── Cart Upsells              # Ofertas no carrinho
├── Single Page Upsells       # Ofertas em páginas de produto
├── Exit-Intent Upsells       # Ofertas de saída
└── Post-Purchase Upsells     # Ofertas pós-compra

Passo 2: Configuração da Oferta
├── Produtos & Targeting       # Seleção de produtos e regras
├── Desconto & Condições      # Tipo e valor do desconto
├── Design & Aparência        # Customização visual
├── Agendamento & Limitações  # Schedule e limites de uso
└── Preview & Teste           # Visualização prévia

Passo 3: Ativação
├── Revisão Final             # Confirmação das configurações
├── Testes de Funcionamento   # Validação automática
└── Ativação                  # Publicação da campanha
```

---

## 🏗️ Melhorias de Arquitetura

### **1. Reestruturação de Diretórios**
```
woo-offers/
├── src/
│   ├── Core/
│   │   ├── Campaign/             # 🆕 Sistema de campanhas
│   │   │   ├── CampaignManager.php
│   │   │   ├── CampaignTypes/
│   │   │   └── CampaignWizard.php
│   │   ├── Offers/               # ✅ Mantido e melhorado
│   │   ├── Security/             # 🆕 Módulo de segurança
│   │   └── Cache/                # 🆕 Sistema de cache
│   ├── Admin/
│   │   ├── Campaign/             # 🆕 Interface de campanhas
│   │   │   ├── Dashboard.php
│   │   │   ├── CampaignList.php
│   │   │   ├── CampaignWizard.php
│   │   │   └── CampaignEditor.php
│   │   ├── Analytics/            # ✅ Melhorado
│   │   └── Settings/             # ✅ Reestruturado
│   ├── Frontend/
│   │   ├── Campaign/             # 🆕 Display de campanhas
│   │   │   ├── CheckoutUpsells.php
│   │   │   ├── CartUpsells.php
│   │   │   ├── SinglePageUpsells.php
│   │   │   └── PostPurchaseUpsells.php
│   │   └── Assets/               # 🆕 Gestão de recursos
│   └── API/
│       ├── Campaign/             # 🆕 Endpoints de campanha
│       └── v2/                   # 🆕 Nova versão da API
├── templates/
│   ├── admin/
│   │   ├── campaign/             # 🆕 Templates de campanha
│   │   └── wizard/               # 🆕 Templates do wizard
│   └── frontend/
│       ├── campaign-types/       # 🆕 Templates por tipo
│       └── components/           # 🆕 Componentes reutilizáveis
└── assets/
    ├── js/
    │   ├── admin/
    │   │   ├── campaign-wizard.js # 🆕 Wizard interativo
    │   │   └── campaign-builder.js # 🆕 Editor de campanhas
    │   └── frontend/
    └── css/
        ├── admin/
        │   └── campaign-ui.css    # 🆕 Estilos modernos
        └── frontend/
```

### **2. Banco de Dados Reestruturado**

#### **Tabela Principal: `wp_woo_campaigns`**
```sql
CREATE TABLE wp_woo_campaigns (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    description text,
    type varchar(50) NOT NULL, -- 'checkout_upsell', 'cart_upsell', etc.
    status varchar(20) NOT NULL DEFAULT 'draft', -- 'draft', 'active', 'paused', 'expired'
    
    -- Configurações da campanha
    settings longtext, -- JSON com todas as configurações
    targeting_rules longtext, -- JSON com regras de targeting
    schedule_config longtext, -- JSON com configurações de agendamento
    design_config longtext, -- JSON com configurações visuais
    
    -- Métricas básicas
    views_count bigint(20) unsigned DEFAULT 0,
    clicks_count bigint(20) unsigned DEFAULT 0,
    conversions_count bigint(20) unsigned DEFAULT 0,
    revenue_generated decimal(10,2) DEFAULT 0.00,
    
    -- Metadados
    priority int(11) NOT NULL DEFAULT 10,
    created_by bigint(20) unsigned NOT NULL,
    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id),
    KEY idx_type_status (type, status),
    KEY idx_priority (priority),
    KEY idx_created_by (created_by),
    KEY idx_schedule (created_at, updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### **Tabela de Analytics: `wp_woo_campaign_analytics`**
```sql
CREATE TABLE wp_woo_campaign_analytics (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    campaign_id bigint(20) unsigned NOT NULL,
    
    -- Identificação do usuário/sessão
    user_id bigint(20) unsigned DEFAULT NULL,
    session_id varchar(100),
    visitor_id varchar(100), -- Para usuarios não logados
    
    -- Dados do evento
    event_type varchar(50) NOT NULL, -- 'view', 'click', 'conversion', 'dismiss'
    event_data longtext, -- JSON com dados específicos do evento
    
    -- Contexto da página
    page_url varchar(500),
    page_type varchar(50), -- 'product', 'cart', 'checkout', etc.
    product_id bigint(20) unsigned DEFAULT NULL,
    order_id bigint(20) unsigned DEFAULT NULL,
    
    -- Valor financeiro
    revenue_impact decimal(10,2) DEFAULT NULL,
    discount_amount decimal(10,2) DEFAULT NULL,
    
    -- Dados técnicos
    user_agent text,
    ip_address varchar(45),
    device_type varchar(20), -- 'desktop', 'mobile', 'tablet'
    
    -- Timestamp
    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id),
    KEY idx_campaign_event (campaign_id, event_type),
    KEY idx_user_session (user_id, session_id),
    KEY idx_temporal (created_at),
    KEY idx_revenue (revenue_impact),
    
    FOREIGN KEY (campaign_id) REFERENCES wp_woo_campaigns(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### **Tabela de A/B Tests: `wp_woo_campaign_tests`**
```sql
CREATE TABLE wp_woo_campaign_tests (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    description text,
    
    -- Configuração do teste
    original_campaign_id bigint(20) unsigned NOT NULL,
    variant_campaigns longtext, -- JSON array com IDs das variações
    traffic_allocation longtext, -- JSON com distribuição de tráfego
    
    -- Métricas de controle
    conversion_goal varchar(50) NOT NULL, -- 'clicks', 'conversions', 'revenue'
    min_confidence_level decimal(5,2) DEFAULT 95.00,
    min_sample_size int(11) DEFAULT 100,
    
    -- Status e timing
    status varchar(20) NOT NULL DEFAULT 'draft',
    winner_campaign_id bigint(20) unsigned DEFAULT NULL,
    start_date datetime DEFAULT NULL,
    end_date datetime DEFAULT NULL,
    
    -- Resultados
    results_data longtext, -- JSON com resultados estatísticos
    
    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id),
    KEY idx_original_campaign (original_campaign_id),
    KEY idx_status_dates (status, start_date, end_date),
    
    FOREIGN KEY (original_campaign_id) REFERENCES wp_woo_campaigns(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 🔒 Melhorias de Segurança

### **1. Correção da Busca de Produtos (PROBLEMA PRINCIPAL)**

#### **❌ Problema Atual (Woo Offers):**
```php
public function search_products_ajax() {
    // ❌ FALTANDO: check_ajax_referer( 'woo_offers_nonce', 'nonce' );
    // ❌ FALTANDO: Verificação de capacidades
    // ❌ Query manual em WP_Query
    
    $query = sanitize_text_field( $_POST['query'] ?? '' );
    // ... resto do código problemático
}
```

#### **✅ Solução Correta (Inspirada no FunnelKit):**
```php
public function search_products_ajax() {
    // ✅ Validação de segurança completa
    SecurityManager::verify_ajax_nonce('woo_offers_search_products');
    SecurityManager::verify_capability('edit_products');
    SecurityManager::check_rate_limit('product_search', 30, 60);
    
    try {
        $query = sanitize_text_field($_POST['query'] ?? '');
        
        if (empty($query) || strlen($query) < 2) {
            wp_send_json_error(__('Query too short', 'woo-offers'));
        }
        
        // ✅ Usar WooCommerce Data Store nativo (como FunnelKit)
        $data_store = WC_Data_Store::load('product');
        $product_ids = $data_store->search_products(
            $query,
            '', // status
            true, // include variations
            false, // return ids only
            20, // limit
            [], // product_ids (empty for all)
            [] // exclude_ids
        );
        
        $products = [];
        foreach ($product_ids as $product_id) {
            $product = wc_get_product($product_id);
            if ($product && $product->is_purchasable()) {
                $products[] = [
                    'id' => $product_id,
                    'name' => $product->get_name(),
                    'price' => $product->get_price_html(),
                    'type' => $product->get_type(),
                    'image' => wp_get_attachment_image_url($product->get_image_id(), 'thumbnail'),
                    'sku' => $product->get_sku(),
                    'stock_status' => $product->get_stock_status()
                ];
            }
        }
        
        wp_send_json_success($products);
        
    } catch (Exception $e) {
        Logger::error('Product search failed', ['error' => $e->getMessage()]);
        wp_send_json_error(__('Search failed. Please try again.', 'woo-offers'));
    }
}
```

### **2. Sistema de Segurança Robusto**

#### **SecurityManager.php**
```php
<?php
namespace WooOffers\Core\Security;

class SecurityManager {
    // Validação de nonces aprimorada
    public static function verify_ajax_nonce($action = 'woo_offers_ajax') {
        if (!wp_verify_nonce($_REQUEST['nonce'] ?? '', $action)) {
            wp_send_json_error([
                'message' => __('Security verification failed.', 'woo-offers'),
                'code' => 'INVALID_NONCE'
            ]);
        }
    }
    
    // Verificação de permissões granular
    public static function verify_capability($capability = 'manage_woocommerce') {
        if (!current_user_can($capability)) {
            wp_send_json_error([
                'message' => __('Insufficient permissions.', 'woo-offers'),
                'code' => 'INSUFFICIENT_PERMISSIONS'
            ]);
        }
    }
    
    // Rate limiting para AJAX
    public static function check_rate_limit($action, $limit = 60, $window = 300) {
        $user_id = get_current_user_id();
        $ip = self::get_client_ip();
        $key = "rate_limit_{$action}_{$user_id}_{$ip}";
        
        $current_count = get_transient($key) ?: 0;
        
        if ($current_count >= $limit) {
            wp_send_json_error([
                'message' => __('Rate limit exceeded. Please try again later.', 'woo-offers'),
                'code' => 'RATE_LIMIT_EXCEEDED'
            ]);
        }
        
        set_transient($key, $current_count + 1, $window);
    }
    
    // Sanitização de dados de campanha
    public static function sanitize_campaign_data($data) {
        return [
            'name' => sanitize_text_field($data['name'] ?? ''),
            'description' => wp_kses_post($data['description'] ?? ''),
            'type' => sanitize_key($data['type'] ?? ''),
            'status' => in_array($data['status'] ?? '', ['draft', 'active', 'paused']) ? $data['status'] : 'draft',
            'settings' => self::sanitize_json_settings($data['settings'] ?? []),
            'targeting_rules' => self::sanitize_targeting_rules($data['targeting_rules'] ?? []),
            'schedule_config' => self::sanitize_schedule_config($data['schedule_config'] ?? []),
            'design_config' => self::sanitize_design_config($data['design_config'] ?? [])
        ];
    }
}
```

---

## 🚀 Melhorias de Performance

### **1. Sistema de Cache Inteligente**

#### **CacheManager.php**
```php
<?php
namespace WooOffers\Core\Cache;

class CacheManager {
    const CACHE_GROUP = 'woo_offers';
    const DEFAULT_EXPIRATION = 12 * HOUR_IN_SECONDS;
    
    // Cache de campanhas ativas
    public static function get_active_campaigns($context = 'all') {
        $cache_key = "active_campaigns_{$context}";
        $campaigns = wp_cache_get($cache_key, self::CACHE_GROUP);
        
        if (false === $campaigns) {
            $campaigns = self::fetch_active_campaigns($context);
            wp_cache_set($cache_key, $campaigns, self::CACHE_GROUP, self::DEFAULT_EXPIRATION);
        }
        
        return $campaigns;
    }
    
    // Cache de busca de produtos (SOLUÇÃO PARA O PROBLEMA DE BUSCA)
    public static function cache_product_search($query, $results) {
        $cache_key = "product_search_" . md5($query);
        wp_cache_set($cache_key, $results, self::CACHE_GROUP, 300); // 5 min cache
    }
    
    public static function get_cached_product_search($query) {
        $cache_key = "product_search_" . md5($query);
        return wp_cache_get($cache_key, self::CACHE_GROUP);
    }
    
    // Invalidação inteligente
    public static function invalidate_campaign($campaign_id) {
        wp_cache_delete("campaign_settings_{$campaign_id}", self::CACHE_GROUP);
        wp_cache_delete_multiple([
            'active_campaigns_all',
            'active_campaigns_checkout',
            'active_campaigns_cart',
            'active_campaigns_product'
        ], self::CACHE_GROUP);
    }
}
```

---

## 🎨 Melhorias de UX/UI

### **1. Interface Moderna (Inspirada no FunnelKit)**

#### **Dashboard de Campanhas:**
```php
<!-- templates/admin/campaign/dashboard.php -->
<div class="woo-offers-dashboard">
    <!-- Header com métricas rápidas -->
    <div class="dashboard-header">
        <div class="metric-cards">
            <div class="metric-card">
                <h3>Campanhas Ativas</h3>
                <div class="metric-value"><?php echo $active_campaigns_count; ?></div>
                <div class="metric-change positive">+12% vs último mês</div>
            </div>
            <div class="metric-card">
                <h3>Taxa de Conversão</h3>
                <div class="metric-value"><?php echo $conversion_rate; ?>%</div>
                <div class="metric-change positive">+2.3% vs último mês</div>
            </div>
            <div class="metric-card">
                <h3>Receita Gerada</h3>
                <div class="metric-value"><?php echo wc_price($total_revenue); ?></div>
                <div class="metric-change positive">+18% vs último mês</div>
            </div>
        </div>
        
        <div class="quick-actions">
            <a href="<?php echo admin_url('admin.php?page=woo-offers-campaigns&action=create'); ?>" 
               class="button button-primary button-large">
                <span class="dashicons dashicons-plus"></span>
                Nova Campanha
            </a>
        </div>
    </div>
    
    <!-- Charts e gráficos -->
    <div class="dashboard-charts">
        <div class="chart-container">
            <canvas id="performance-chart"></canvas>
        </div>
        <div class="chart-container">
            <canvas id="conversion-funnel"></canvas>
        </div>
    </div>
    
    <!-- Campanhas recentes -->
    <div class="recent-campaigns">
        <h2>Campanhas Recentes</h2>
        <div class="campaigns-grid">
            <!-- Loop de campanhas -->
        </div>
    </div>
</div>
```

### **2. JavaScript Moderno e Funcional**

#### **Busca de Produtos Corrigida:**
```javascript
class ProductSearch {
    constructor(container) {
        this.container = container;
        this.searchInput = container.querySelector('.product-search-input');
        this.resultsContainer = container.querySelector('.search-results');
        this.selectedProducts = [];
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.setupDebouncing();
    }
    
    bindEvents() {
        this.searchInput.addEventListener('input', (e) => {
            this.debouncedSearch(e.target.value);
        });
    }
    
    setupDebouncing() {
        this.debouncedSearch = this.debounce((query) => {
            this.searchProducts(query);
        }, 300);
    }
    
    async searchProducts(query) {
        if (query.length < 2) {
            this.hideResults();
            return;
        }
        
        this.showLoading();
        
        try {
            const response = await fetch(wooOffersAdmin.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'woo_offers_search_products',
                    nonce: wooOffersAdmin.searchNonce, // ✅ Nonce específico
                    query: query
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.displayResults(result.data);
            } else {
                this.showError(result.data.message || 'Search failed');
            }
        } catch (error) {
            console.error('Search error:', error);
            this.showError('Network error occurred');
        }
    }
    
    displayResults(products) {
        this.hideLoading();
        
        if (products.length === 0) {
            this.showNoResults();
            return;
        }
        
        const html = products.map(product => `
            <div class="product-result" data-id="${product.id}">
                <div class="product-image">
                    <img src="${product.image}" alt="${product.name}">
                </div>
                <div class="product-info">
                    <h4>${product.name}</h4>
                    <div class="product-meta">
                        <span class="price">${product.price}</span>
                        <span class="sku">SKU: ${product.sku}</span>
                    </div>
                </div>
                <button class="select-product" data-id="${product.id}">
                    Selecionar
                </button>
            </div>
        `).join('');
        
        this.resultsContainer.innerHTML = html;
        this.showResults();
        
        // Bind selection events
        this.resultsContainer.querySelectorAll('.select-product').forEach(button => {
            button.addEventListener('click', (e) => {
                this.selectProduct(e.target.dataset.id);
            });
        });
    }
    
    // Utility methods
    debounce(func, wait) {
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
}
```

---

## 📊 Sistema de Analytics Avançado

### **1. Tracking Inteligente**

#### **AnalyticsManager.php:**
```php
<?php
namespace WooOffers\Core\Analytics;

class AnalyticsManager {
    private static $instance = null;
    
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // Track eventos com contexto rico
    public function track_event($campaign_id, $event_type, $context = []) {
        global $wpdb;
        
        $data = [
            'campaign_id' => $campaign_id,
            'user_id' => get_current_user_id() ?: null,
            'session_id' => $this->get_session_id(),
            'visitor_id' => $this->get_visitor_id(),
            'event_type' => $event_type,
            'event_data' => json_encode($context),
            'page_url' => $_SERVER['REQUEST_URI'] ?? '',
            'page_type' => $this->detect_page_type(),
            'product_id' => $context['product_id'] ?? null,
            'order_id' => $context['order_id'] ?? null,
            'revenue_impact' => $context['revenue'] ?? null,
            'discount_amount' => $context['discount'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'ip_address' => $this->get_client_ip(),
            'device_type' => $this->detect_device_type(),
            'created_at' => current_time('mysql')
        ];
        
        $wpdb->insert(
            $wpdb->prefix . 'woo_campaign_analytics',
            $data
        );
        
        // Atualizar contadores em tempo real
        $this->update_campaign_counters($campaign_id, $event_type, $context);
    }
    
    // Relatórios de performance
    public function get_campaign_performance($campaign_id, $date_range = 30) {
        global $wpdb;
        
        $cache_key = "campaign_performance_{$campaign_id}_{$date_range}";
        $cached = wp_cache_get($cache_key, 'woo_offers');
        
        if (false !== $cached) {
            return $cached;
        }
        
        $sql = "
            SELECT 
                event_type,
                COUNT(*) as count,
                COALESCE(SUM(revenue_impact), 0) as total_revenue,
                COALESCE(AVG(revenue_impact), 0) as avg_revenue,
                COUNT(DISTINCT visitor_id) as unique_visitors
            FROM {$wpdb->prefix}woo_campaign_analytics
            WHERE campaign_id = %d
              AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
            GROUP BY event_type
        ";
        
        $results = $wpdb->get_results($wpdb->prepare($sql, $campaign_id, $date_range));
        
        wp_cache_set($cache_key, $results, 'woo_offers', 300); // 5 min cache
        
        return $results;
    }
}
```

---

## 📋 Plano de Implementação

### **Fase 1: Correções Críticas (1-2 semanas)**
1. **✅ CORRIGIR BUSCA DE PRODUTOS** (problema principal)
   - Implementar validação de nonce
   - Usar WooCommerce Data Store
   - Adicionar rate limiting
   - Melhorar tratamento de erros

2. **Implementar SecurityManager**
   - Validações robustas
   - Rate limiting
   - Sanitização aprimorada

3. **Otimizar performance básica**
   - Cache de busca de produtos
   - Otimização de queries

### **Fase 2: Novo Sistema de Campanhas (4-6 semanas)**
1. **Reestruturação de banco de dados**
2. **CampaignManager e sistema de tipos**
3. **Interface básica do wizard**
4. **Templates de frontend**

### **Fase 3: Features Avançadas (4-6 semanas)**
1. **Sistema de analytics**
2. **A/B testing**
3. **Dashboard moderno**
4. **REST API v2**

### **Fase 4: Polish & Lançamento (2-3 semanas)**
1. **Testes extensivos**
2. **Migração de dados**
3. **Documentação**
4. **Performance testing**

---

## 🎯 Benefícios da Refatoração

### **Problemas Resolvidos:**
- ✅ **Busca de produtos funcionando** (problema principal)
- ✅ **Segurança robusta** com validações completas
- ✅ **Performance otimizada** com cache inteligente
- ✅ **UX moderna** inspirada no FunnelKit
- ✅ **Arquitetura escalável** e manutenível

### **Novos Recursos:**
- 🆕 **Sistema de campanhas** intuitivo
- 🆕 **Analytics avançados** com gráficos
- 🆕 **A/B testing** automatizado
- 🆕 **Templates prontos** para uso
- 🆕 **API REST** para integrações

### **Metas de Performance:**
- **Busca de produtos:** < 200ms
- **Cache hit rate:** > 90%
- **Conversion rate:** +20% vs versão atual
- **User satisfaction:** > 4.5/5

Esta refatoração transformará o Woo Offers em uma solução profissional e competitiva, resolvendo os problemas atuais e adicionando recursos avançados inspirados nas melhores práticas do mercado. 