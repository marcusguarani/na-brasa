<nav class="navbar navbar-expand-lg app-navbar">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="/" aria-label="NaBrasa - início"><img class="brand-logo" src="/assets/img/nabrasa-logo.png" alt=""> <span>Na<span class="brand">Brasa</span></span></a>
        <?php if (isset($_SESSION['usuario_id'])): ?>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse justify-content-end" id="mainNav">
                <div class="navbar-nav align-items-lg-center gap-lg-2 mt-3 mt-lg-0">
                    <a class="nav-link active" href="/"><i class="bi bi-grid-1x2-fill me-1"></i> Dashboard</a>
                    <a class="btn btn-sm btn-primary px-3" href="/pages/churrasco_form.php"><i class="bi bi-plus-lg me-1"></i> Novo churrasco</a>
                    <a class="nav-link ms-lg-2" href="/logout.php"><i class="bi bi-box-arrow-right me-1"></i> Sair</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</nav>
