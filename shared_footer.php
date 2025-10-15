<?php
// Archivo: views/shared_footer.php
// Footer compartido para todas las vistas
?>
<footer class="main-footer">
    <div class="footer-content">
        <div class="footer-info">
            <p>&copy; <?php echo date('Y'); ?> Sistema de Gestión de Ventas. Todos los derechos reservados.</p>
        </div>
        <div class="footer-links">
            <a href="#" class="footer-link">Términos de Uso</a>
            <a href="#" class="footer-link">Política de Privacidad</a>
            <a href="#" class="footer-link">Soporte</a>
        </div>
    </div>
</footer>

<style>
.main-footer {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px 0;
    margin-top: 40px;
    border-top: 1px solid #e9ecef;
}

.footer-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.footer-info p {
    margin: 0;
    font-size: 14px;
    opacity: 0.9;
}

.footer-links {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.footer-link {
    color: white;
    text-decoration: none;
    font-size: 14px;
    opacity: 0.8;
    transition: opacity 0.2s;
}

.footer-link:hover {
    opacity: 1;
    text-decoration: underline;
}

@media (max-width: 768px) {
    .footer-content {
        flex-direction: column;
        text-align: center;
    }
    
    .footer-links {
        justify-content: center;
    }
}
</style>
