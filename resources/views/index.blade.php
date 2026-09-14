<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Hotel Pinar del Río</title>
    <meta name="description" content="">
    <meta name="keywords" content="">

    <!-- Favicons -->
    <link href="{{ asset('assets/img/favicon.png') }}" rel="icon" type="image/png">
    <link href="{{ asset('assets/img/favicon.png') }}" rel="shortcut icon" type="image/png">
    <link href="{{ asset('assets/img/apple-touch-icon.png') }}" rel="apple-touch-icon">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Josefin+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;1,100;1,200;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/aos/aos.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/glightbox/css/glightbox.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/swiper/swiper-bundle.min.css') }}" rel="stylesheet">

    <!-- Main CSS File -->
    <link href="{{ asset('assets/css/main.css') }}" rel="stylesheet">
    <style>
        .contact-form-container {
            padding: 2rem;
            border-radius: 0.75rem;
            background: #fff;
            box-shadow: 0 0.5rem 2rem rgba(28, 39, 48, 0.12);
        }

        .contact-form-container .form-control {
            min-height: 3rem;
            padding: 0.75rem 1rem;
            border: 1px solid #d9e0e5;
            border-radius: 0.4rem;
        }

        .contact-form-container textarea.form-control {
            min-height: 9rem;
            resize: vertical;
        }

        .contact-form-container .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
        }

        .contact-form-container button[type="submit"] {
            padding: 0.75rem 1.5rem;
            border: 0;
            border-radius: 0.4rem;
            color: #fff;
            background: #0d6efd;
            cursor: pointer;
        }
    </style>

  <!-- =======================================================
  * Template Name: Grandoria
  * Template URL: https://bootstrapmade.com/grandoria-bootstrap-hotel-template/
  * Updated: Jul 29 2025 with Bootstrap v5.3.7
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body class="index-page">

    <header id="header" class="header sticky-top">

        <div class="topbar d-flex align-items-center dark-background">
            <div class="container d-flex justify-content-center justify-content-md-between">
                <div class="contact-info d-flex align-items-center">
                    <i class="bi bi-envelope d-flex align-items-center"><a
                            href="mailto:turismo@amep.org.ar">turismo@amep.org.ar</a></i>
                    <i class="bi bi-whatsapp d-flex align-items-center ms-4"><span>+54 9 3548 61-8197</span></i>
                    <i class="bi bi-telephone d-flex align-items-center ms-4"><span>+54 9 3548 489880</span></i>
                </div>
                <div class="social-links d-none d-md-flex align-items-center">
                    <a href="https://www.facebook.com/amepmutual" class="facebook"><i class="bi bi-facebook"></i></a>
                    <a href="https://www.instagram.com/turismo.amep" class="instagram"><i class="bi bi-instagram"></i></a>
                </div>
            </div>
        </div><!-- End Top Bar -->

        <div class="branding d-flex align-items-cente">

            <div class="container position-relative d-flex align-items-center justify-content-between">
                <a href="{{ route('login') }}" class="logo d-flex align-items-center">
                    <!-- Uncomment the line below if you also wish to use an image logo -->
                    <img src="assets/img/pinarverde.png" alt="Hotel Pinar del Río Logo" class="img-fluid"
                        style="width: 50px; height: 100px;">
                    <h1 class="sitename">Hotel Pinar del Río</h1>
                </a>

                <nav id="navmenu" class="navmenu">
                    <ul>
                        <li><a href="#hotel-hero" class="active">Inicio</a></li>
                        <li><a href="#rooms-showcase">Habitaciones</a></li>
                        <li><a href="#amenities-cards">Servicios</a></li>
                        <li><a href="#location-section">Ubicación</a></li>
                        <li><a href="#contact-section">Contacto</a></li>
                    </ul>
                    <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
                </nav>

            </div>

        </div>

    </header>

    <main class="main">

        <!-- Hotel Hero Section -->
        <section id="hotel-hero" class="hotel-hero section">

            <div class="container" data-aos="fade-up" data-aos-delay="100">

                <div class="row gy-4 align-items-center">

                    <div class="col-lg-6" data-aos="fade-right" data-aos-delay="200">
                        <div class="hero-content">
                            <h1>Combine la naturaleza y el confort de su hogar en un solo lugar</h1>
                            <p class="lead">En Hotel Pinar del Río, queremos que se sienta en casa desde el primer momento. Situado como un balcón natural frente al imponente Cerro Uritorco, nuestro hotel le invita a vivir una experiencia de descanso absoluto, donde la vista es la protagonista de sus mañanas y atardeceres.</p>
                            <p class="lead">Aquí, la tranquilidad se respira en cada rincón. Disfrute de un ambiente familiar y cercano. Más que un hospedaje, somos su lugar de confianza en las sierras.</p>
                            <div class="hero-features">
                                <div class="feature-item">
                                    <i class="bi bi-wifi"></i>
                                    <span>WiFi gratuito</span>
                                </div>
                                <div class="feature-item">
                                    <i class="bi bi-car-front"></i>
                                    <span>Cocheras cubiertas</span>
                                </div>
                            </div>
                            <div class="hero-buttons">
                                <a href="{{url('login')}}" class="btn btn-primary">Reservar Ahora</a>
                                <a href="#rooms-showcase" class="btn btn-outline">Habitaciones</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6" data-aos="fade-left" data-aos-delay="300">
                        <div class="hero-images">
                            <div class="main-image">
                                <img src="assets/img/DSC_0485.jpg" alt="Pinar Hotel" class="img-fluid">
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </section><!-- /Hotel Hero Section -->

        <!-- About Home Section -->
        <section id="about-home" class="about-home section light-background">

            <div class="container" data-aos="fade-up" data-aos-delay="100">

                <div class="row gy-4 align-items-center">

                    <div class="col-lg-6" data-aos="fade-right" data-aos-delay="200">
                        <div class="about-content">
                            <h2>Bienvenido al Hotel Pinar del Río</h2>
                            <p class="lead">Donde la tranquilidad se encuentra con el corazón del paraíso
                                natural.</p>
                            <p>Con una ubicación privilegiada en el corazón de Capilla del Monte, el Hotel Pinar del Río combina la comodidad urbana con la paz de la naturaleza. Situados a solo dos cuadras de la reconocida Calle Techada, le ofrecemos la libertad de disfrutar del paseo más icónico de la ciudad a pie, manteniendo la tranquilidad de nuestro 'balcón natural' frente al Cerro Uritorco.</p>
                            <p>Respaldados por más de tres décadas de trayectoria, nuestras puertas están abiertas a todo el público, invitando a cada viajero a descubrir la calidez de nuestro servicio. Además, mantenemos un compromiso especial con nuestra casa: los socios de AMEP disfrutan de un beneficio diferencial con un importante descuento exclusivo.</p>
                            <p>Venga a vivir una experiencia donde la cercanía, las vistas panorámicas y el confort se unen para garantizarle el descanso que merece.</p>

                            <div class="about-actions">
                                <a href="#rooms-showcase" class="btn-secondary">Ver Habitaciones</a>
                            </div>
                        </div>
                    </div><!-- End About Content -->

                    <div class="col-lg-6" data-aos="fade-left" data-aos-delay="300">
                        <div class="about-images">
                            <div class="main-image">
                                <img src="assets/img/DSC_0390.jpg" alt="Vista Principal del Hotel Pinar del Río"
                                    class="img-fluid">
                            </div>
                            <div class="secondary-image">
                                <img src="assets/img/DSC_0383.jpg" alt="Interior de Suite de Lujo" class="img-fluid">
                            </div>
                            <div class="experience-badge">
                                <div class="badge-content">
                                    <span class="badge-number">30+</span>
                                    <span class="badge-text">Años<br>de Experiencia</span>
                                </div>
                            </div>
                        </div>
                    </div><!-- End About Images -->

                </div>

            </div>

        </section><!-- /About Home Section -->

        <!-- Rooms Showcase Section -->
        <section id="rooms-showcase" class="rooms-showcase section">

            <!-- Section Title -->
            <div class="container section-title" data-aos="fade-up">
                <span class="description-title">Habitaciones</span>
                <h2>Habitaciones</h2>
                <p>Habitaciones con encanto y vistas privilegiadas, nuestras habitaciones están diseñadas para ofrecerle el descanso reparador que busca tras un día de paseo por Capilla del Monte. Sencillas, acogedoras y equipadas con todo lo necesario para que se sienta como en casa.</p>
            </div><!-- End Section Title -->

            <div class="container" data-aos="fade-up" data-aos-delay="100">

                <div class="row gy-5">
                    <div class="col-xl-8" data-aos="zoom-in" data-aos-delay="200">
                        <div class="hero-room-showcase">
                            <div class="showcase-image-container">
                                <img src="assets/img/DSC_0397.jpg" alt="Grand Presidential Suite" class="img-fluid">
                                <div class="room-category-badge">
                                    <span>Habitación Doble</span>
                                </div>
                                <div class="room-details-overlay">
                                    <div class="room-specs">
                                        <span class="spec-item">
                                            <i class="bi bi-people"></i>
                                            <span>2 Huéspedes</span>
                                        </span>
                                        <span class="spec-item">
                                            <i class="bi bi-house"></i>
                                            <span>20m²</span>
                                        </span>
                                        <span class="spec-item">
                                            <i class="bi bi-geo-alt"></i>
                                            <span>Vista al Cerro Uritorco</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="showcase-content">
                                <div class="room-title-section">
                                    <h2>Hermosas vistas y comodidad</h2>
                                    <div class="room-rating">
                                        <div class="stars">
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-fill"></i>
                                        </div>
                                        <span class="rating-text">5.0 Excellence</span>
                                    </div>
                                </div>
                                <p class="room-description">Para quienes desean una experiencia visual única, contamos con un número selecto de habitaciones con vista frontal al Cerro Uritorco. Al ser nuestras unidades más solicitadas y limitadas, recomendamos reservar con antelación para asegurar el privilegio de despertar frente a esta postal inolvidable.</p>
                                <div class="amenities-grid">
                                    <div class="amenity-item">
                                        <i class="bi bi-wifi"></i>
                                        <span>WiFi en todo el hotel</span>
                                    </div>
                                    <div class="amenity-item">
                                        <i class="bi bi-tv"></i>
                                        <span>TV por cable</span>
                                    </div>
                                    <div class="amenity-item">
                                        <i class="bi bi-cup-hot"></i>
                                        <span>Bar de Café</span>
                                    </div>
                                    <div class="amenity-item">
                                        <i class="bi bi-snow"></i>
                                        <span>Control de Clima</span>
                                    </div>
                                </div>
                                <div class="booking-section">
                                    <div class="price-display">
                                    <span class="currency">$</span>
                                    <span class="amount">0,00</span>
                                    <span class="period">por noche</span>
                                </div>
                                    <a href="room-details.html" class="primary-booking-btn">Reserve ahora</a>
                                </div>
                            </div>
                        </div>
                    </div><!-- End Hero Room -->

                    <div class="col-xl-4">
                        <div class="room-list-container">
                            <div class="standard-room-card" data-aos="slide-left" data-aos-delay="250">
                                <div class="card-image">
                                    <img src="assets/img/DSC_0383.jpg" alt="Executive Room" class="img-fluid">
                                    <div class="view-link">
                                        <i class="bi bi-arrow-up-right"></i>
                                    </div>
                                </div>
                                <div class="card-content">
                                    <h4>Habitación Triple</h4>
                                    <p>Comodidad y cálidas con vista al Cerro Uritorco. Desayuno incluido. Incluye ropa blanca y Servicio de Habitación.</p>
                                    <div class="booking-row">
                                        <div class="price">$0,00<small>/por noche</small></div>
                                    </div>
                                </div>
                            </div><!-- End Standard Room -->

                            <div class="standard-room-card" data-aos="slide-left" data-aos-delay="300">
                                <div class="card-image">
                                    <img src="assets/img/DSC_0398.jpg" alt="Garden View" class="img-fluid">
                                    <div class="view-link">
                                        <i class="bi bi-arrow-up-right"></i>
                                    </div>
                                </div>
                                <div class="card-content">
                                    <h4>Habitación Cuádruple</h4>
                                    <p>Amplitud y limpieza. Desayuno incluido. Incluye ropa blanca y Servicio de Habitación.</p>
                                    <div class="booking-row">
                                        <div class="price">$0,00<small>/por noche</small></div>
                                    </div>
                                </div>
                            </div><!-- End Standard Room -->

                            <div class="standard-room-card" data-aos="slide-left" data-aos-delay="350">
                                <div class="card-image">
                                    <img src="assets/img/DSC_0408.jpg" alt="Family Suite" class="img-fluid">
                                    <div class="view-link">
                                        <i class="bi bi-arrow-up-right"></i>
                                    </div>
                                </div>
                                <div class="card-content">
                                    <h4>Departamento Cuádruple</h4>
                                    <p>Espacio para toda tu familia. Desayuno incluido. Incluye ropa blanca y Servicio de Habitación.</p>
                                    <div class="booking-row">
                                        <div class="price">$0,00<small>/por noche</small></div>
                                    </div>
                                </div>
                            </div><!-- End Standard Room -->

                        </div>
                    </div>

                </div>
            </div>

        </section><!-- /Rooms Showcase Section -->

        <!-- Amenities Cards Section -->
        <section id="amenities-cards" class="amenities-cards section">

            <!-- Section Title -->
            <div class="container section-title" data-aos="fade-up">
                <span class="description-title">Servicios</span>
                <h2>Servicios</h2>
                Disfrute de nuestras instalaciones de primer nivel, diseñadas para brindarle una experiencia inolvidable
                con servicios excepcionales y comodidades modernas
            </div><!-- End Section Title -->

            <div class="container" data-aos="fade-up" data-aos-delay="100">

                <div class="row g-5">

                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                        <div class="facility-card">
                            <div class="facility-image">
                                <img src="assets/img/DSC_4859.jpg" alt="Wireless Internet" class="img-fluid">
                                <div class="facility-overlay">
                                    <i class="bi bi-droplet"></i>
                                </div>
                            </div>
                            <div class="facility-info">
                                <h4>Piscinas Climatizadas y Solárium</h4>
                                <p>Disfrute del agua a la temperatura perfecta en todo momento. Tanto nuestra piscina principal como la piscina para niños son climatizadas y a cielo abierto (disponibles de noviembre a marzo), para que grandes y chicos aprovechen el sol de las sierras con el máximo confort. Para su total tranquilidad, ofrecemos servicio de guardavidas presencial, garantizando diversión segura para los más pequeños y un descanso sin preocupaciones para usted.</p>
                            </div>
                        </div>
                    </div><!-- End Facility Card -->

                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="150">
                        <div class="facility-card">
                            <div class="facility-image">
                                <img src="assets/img/DSC_4860.jpg" alt="Swimming Pool" class="img-fluid">
                                <div class="facility-overlay">
                                    <i class="bi bi-brightness-high"></i>
                                </div>
                            </div>
                            <div class="facility-info">
                                <h4>Solárium y Descanso al Aire Libre</h4>
                                <p>Complete su experiencia de bienestar en nuestro amplio solárium junto a las piscinas. Un ambiente tranquilo, pensado para que disfrute del sol y el aire puro con total comodidad. La combinación ideal entre agua climatizada, descanso y la inmejorable postal de las sierras cordobesas.</p>
                            </div>
                        </div>
                    </div><!-- End Facility Card -->

                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                        <div class="facility-card">
                            <div class="facility-image">
                                <img src="assets/img/DSC_0426.jpg" alt="Valet Parking" class="img-fluid">
                                <div class="facility-overlay">
                                    <i class="bi bi-image-alt"></i>
                                </div>
                            </div>
                            <div class="facility-info">
                                <h4>Jardín y Espacios de Relax</h4>
                                <p>Nuestro jardín es mucho más que un espacio verde: es el corazón social del hotel. Ubicado estratégicamente alrededor del área de nuestro bar y con el Cerro Uritorco siempre a la vista, este sector está diseñado para que la naturaleza acompañe sus momentos de descanso. Es el rincón ideal para sentarse a conversar, disfrutar de una bebida o simplemente dejarse llevar por la calma del entorno.</p>
                            </div>
                        </div>
                    </div><!-- End Facility Card -->

                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="250">
                        <div class="facility-card">
                            <div class="facility-image">
                                <img src="assets/img/DSC_0439.jpg" alt="Fitness Center" class="img-fluid">
                                <div class="facility-overlay">
                                    <i class="bi bi-cup-hot"></i>
                                </div>
                            </div>
                            <div class="facility-info">
                                <h4>Desayuno</h4>
                                <p>Ofrecemos desayuno buffet completo con productos frescos y regionales, almuerzos y
                                    cenas a la carta con menú variado que incluye opciones tradicionales argentinas y
                                    platos internacionales.</p>
                            </div>
                        </div>
                    </div><!-- End Facility Card -->

                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                        <div class="facility-card">
                            <div class="facility-image">
                                <img src="assets/img/DSC_0474.jpg" alt="Comedor" class="img-fluid">
                                <div class="facility-overlay">
                                    <i class="bi bi-fork-knife"></i>
                                </div>
                            </div>
                            <div class="facility-info">
                                <h4>Comedor</h4>
                                <p>Disfrute de exquisitas comidas en nuestro elegante comedor con capacidad para todos
                                    nuestros huéspedes. Nuestro chef prepara cada plato con ingredientes de primera
                                    calidad, garantizando una experiencia gastronómica memorable en un ambiente acogedor
                                    y familiar.</p>
                            </div>
                        </div>
                    </div><!-- End Facility Card -->

                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="350">
                        <div class="facility-card">
                            <div class="facility-image">
                                <img src="assets/img/DSC_0390.jpg" alt="Sala de Estar y Lectura" class="img-fluid">
                                <div class="facility-overlay">
                                    <i class="bi bi-tv"></i>
                                </div>
                            </div>
                            <div class="facility-info">
                                <h4>Sala de Estar y Lectura</h4>
                                <p>Un espacio interior diseñado para sus momentos de pausa. Nuestra sala cuenta con TV y un ambiente cálido y acogedor, perfecto para sumergirse en una buena lectura, planificar las excursiones del día siguiente o compartir una charla tranquila en familia.</p>
                            </div>
                        </div>
                    </div><!-- End Facility Card -->

                </div>

            </div>

        </section><!-- /Amenities Cards Section -->

        <!-- Location Section -->
        <section id="location-section" class="location-section section light-background">

            <!-- Section Title -->
            <div class="container section-title" data-aos="fade-up">
                <span class="description-title">Ubicación</span>
                <h2>Ubicación</h2>
                <p>Descubra nuestra privilegiada ubicación en el corazón de Capilla del Monte, con fácil acceso a las
                    principales atracciones turísticas de la zona</p>
            </div><!-- End Section Title -->

            <div class="container" data-aos="fade-up" data-aos-delay="100">

                <div class="row gy-4 align-items-center">

                    <div class="col-lg-4" data-aos="fade-right" data-aos-delay="200">
                        <div class="location-info">
                            <h3>Hotel Pinar del Río</h3>
                            <div class="location-details">
                                <div class="detail-item">
                                    <i class="bi bi-geo-alt-fill"></i>
                                    <div class="detail-content">
                                        <h5>Dirección</h5>
                                        <p>Sarmiento 284 esq. Rivadavia<br>Capilla del Monte, Córdoba, Argentina</p>
                                    </div>
                                </div>
                                <div class="detail-item">
                                    <i class="bi bi-telephone-fill"></i>
                                    <div class="detail-content">
                                        <h5>Teléfono</h5>
                                        <p>+54 9 3548 489880</p>
                                    </div>
                                </div>
                                <div class="detail-item">
                                    <i class="bi bi-whatsapp"></i>
                                    <div class="detail-content">
                                        <h5>WhatsApp</h5>
                                        <p>+54 9 342 598-3527 de Lun. a Vie 8.00hs a 16.15hs</p>
                                        <a href="https://wa.me/5493425983527" target="_blank" class="whatsapp-btn">
                                            <i class="bi bi-whatsapp"></i>
                                            Enviar WhatsApp
                                        </a>
                                        <h6></h6>
                                        <p>+54 9 3548 61-8197 las 24hs.</p>
                                        <a href="https://wa.me/5493548618197" target="_blank" class="whatsapp-btn">
                                            <i class="bi bi-whatsapp"></i>
                                            Enviar WhatsApp
                                        </a>
                                    </div>
                                </div>
                                <div class="detail-item mt-4">
                                    <i class="bi bi-envelope-fill"></i>
                                    <div class="detail-content">
                                        <h5>Email</h5>
                                        <p>turismo@amep.org.ar</p>
                                    </div>
                                </div>
                            </div>
                            <div class="location-features">
                                <h4>Cerca de:</h4>
                                <div class="features-grid">
                                    <div class="feature-item">
                                        <i class="bi bi-camera"></i>
                                        <span>Cerro Uritorco - 15 min</span>
                                    </div>
                                    <div class="feature-item">
                                        <i class="bi bi-shop"></i>
                                        <span>Centro Comercial - 3 min</span>
                                    </div>
                                    <div class="feature-item">
                                        <i class="bi bi-bus-front"></i>
                                        <span>Terminal de Ómnibus - 10 min</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- End Location Info -->

                    <div class="col-lg-8" data-aos="fade-left" data-aos-delay="400">
                        <div class="map-container" data-aos="fade-up" data-aos-delay="400">
                            <div class="google-map">
                                <iframe
                                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d4843.8304082899!2d-64.52469480534201!3d-30.855725959803358!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x942d8d8808d30df1%3A0xe9f81f842c4b85d4!2sHotel%20Pinar%20del%20Rio!5e0!3m2!1ses!2sar!4v1768497515889!5m2!1ses!2sar"
                                    width="100%" height="600" style="border:0; border-radius: 10px;"
                                    allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                                </iframe>
                            </div>
                        </div>
                    </div><!-- End Location Image -->

                </div>
            </div>

        </section><!-- /Location Section -->

        <!-- Contact Section -->
        <section id="contact-section" class="contact-section section">

            <!-- Section Title -->
            <div class="container section-title" data-aos="fade-up">
                <span class="description-title">Contacto</span>
                <h2>Contáctenos</h2>
                <p>Estamos aquí para hacer de su estadía una experiencia inolvidable. Póngase en contacto con nosotros
                    para reservas, consultas o cualquier información adicional</p>
            </div><!-- End Section Title -->

            <div class="container" data-aos="fade-up" data-aos-delay="100">

                <div class="row gy-4">

                    <div class="col-lg-6" data-aos="fade-right" data-aos-delay="200">
                        <div class="contact-form-container">
                            <h3>Envíanos un Mensaje</h3>

                            @if (session('success'))
                                <div class="alert alert-success mb-3">
                                    {{ session('success') }}
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-danger mb-3">
                                    {{ session('error') }}
                                </div>
                            @endif

                            <form action="{{ route('contact.send') }}" method="post" class="php-email-form">
                                @csrf
                                <div class="row gy-3">
                                    <div class="col-md-6">
                                        <input type="text" name="name" class="form-control"
                                            placeholder="Su Nombre" required>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="email" name="email" class="form-control"
                                            placeholder="Su Email" required>
                                    </div>
                                    <div class="col-12">
                                        <input type="text" name="subject" class="form-control"
                                            placeholder="Asunto" required>
                                    </div>
                                    <div class="col-12">
                                        <textarea class="form-control" name="message" rows="6" placeholder="Mensaje" required></textarea>
                                    </div>
                                    <div class="col-12">
                                        <div class="loading">Enviando...</div>
                                        <div class="error-message"></div>
                                        <div class="sent-message">Su mensaje ha sido enviado. ¡Gracias!</div>
                                        <button type="submit">Enviar Mensaje</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div><!-- End Contact Form -->

                    <div class="col-lg-6" data-aos="fade-left" data-aos-delay="300">
                        <div class="contact-info-container">
                            <h3>Información de Contacto</h3>
                            <div class="contact-info-list">

                                <div class="contact-info-item">
                                    <div class="contact-icon">
                                        <i class="bi bi-whatsapp"></i>
                                    </div>
                                    <div class="detail-content">
                                        <h5>WhatsApp</h5>
                                        <p>+54 9 342 598-3527 de Lun. a Vie 8.00hs a 16.15hs</p>
                                        <a href="https://wa.me/5493425983527" target="_blank" class="whatsapp-btn">
                                            <i class="bi bi-whatsapp"></i>
                                            Enviar WhatsApp
                                        </a>
                                        <h6></h6>
                                        <p>+54 9 3548 61-8197 las 24hs.</p>
                                        <a href="https://wa.me/5493548618197" target="_blank" class="whatsapp-btn">
                                            <i class="bi bi-whatsapp"></i>
                                            Enviar WhatsApp
                                        </a>
                                    </div>
                                </div>

                                <div class="contact-info-item mt-4">
                                    <div class="contact-icon">
                                        <i class="bi bi-envelope-fill"></i>
                                    </div>
                                    <div class="contact-content">
                                        <h4>Email</h4>
                                        <p>turismo@amep.org.ar</p>
                                    </div>
                                </div>

                                <div class="contact-info-item">
                                    <div class="contact-icon">
                                        <i class="bi bi-clock-fill"></i>
                                    </div>
                                    <div class="contact-content">
                                        <h4>Horarios de Atención</h4>
                                        <p>Lunes - Domingo: 24 horas<br>Recepción disponible las 24hs</p>
                                    </div>
                                </div>
                            </div>

                            <div class="social-links-contact">
                                <h4>Síguenos</h4>
                                <div class="social-icons">
                                    <a href="#" class="social-link facebook">
                                        <i class="bi bi-facebook"></i>
                                    </a>
                                    <a href="#" class="social-link instagram">
                                        <i class="bi bi-instagram"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div><!-- End Contact Info -->

                </div>

                <div class="row mt-5">
                    <div class="col-12">
                        <div class="contact-cta" data-aos="fade-up" data-aos-delay="400">
                            <div class="cta-content">
                                <h3>¿Listo para Reservar?</h3>
                                <p>Reserve ahora y disfrute de una experiencia única en el Hotel Pinar del Río</p>
                                <div class="cta-buttons">
                                    <a href="tel:+5493548481022" class="btn-cta primary me-4">
                                        <i class="bi bi-telephone-fill"></i>
                                        Llamar Ahora
                                    </a>
                                    <a href="https://wa.me/5493425983527" target="_blank" class="btn-cta secondary">
                                        <i class="bi bi-whatsapp"></i>
                                        WhatsApp
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </section><!-- /Contact Section -->

        <!-- Gallery Showcase Section -->
        <section id="gallery-showcase" class="gallery-showcase section">

            <div class="container" data-aos="fade-up" data-aos-delay="100">

                <div class="gallery-carousel swiper init-swiper" data-aos="fade-up" data-aos-delay="200">
                    <script type="application/json" class="swiper-config">
            {
              "loop": true,
              "speed": 600,
              "autoplay": {
                "delay": 3000
              },
              "slidesPerView": 1,
              "spaceBetween": 20,
              "centeredSlides": true,
              "breakpoints": {
                "576": {
                  "slidesPerView": 2,
                  "centeredSlides": false
                },
                "768": {
                  "slidesPerView": 3,
                  "centeredSlides": false
                },
                "992": {
                  "slidesPerView": 4,
                  "centeredSlides": false
                },
                "1200": {
                  "slidesPerView": 5,
                  "centeredSlides": false
                }
              }
            }
          </script>
                    <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            <div class="gallery-item">
                                <img src="assets/img/DSC_0383.jpg" alt="Luxurious Suite" class="img-fluid"
                                    loading="lazy">
                                <a href="assets/img/DSC_0383.jpg" class="gallery-overlay glightbox">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="gallery-item">
                                <img src="assets/img/DSC_0385.jpg" alt="Modern Lobby" class="img-fluid"
                                    loading="lazy">
                                <a href="assets/img/DSC_0385.jpg" class="gallery-overlay glightbox">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="gallery-item">
                                <img src="assets/img/DSC_0390.jpg" alt="Elegant Dining Area"
                                    class="img-fluid" loading="lazy">
                                <a href="assets/img/DSC_0390.jpg" class="gallery-overlay glightbox">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="gallery-item">
                                <img src="assets/img/DSC_0426.jpg" alt="Grand Ballroom Setup"
                                    class="img-fluid" loading="lazy">
                                <a href="assets/img/DSC_0426.jpg" class="gallery-overlay glightbox">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="gallery-item">
                                <img src="assets/img/DSC_0439.jpg" alt="Relaxing Poolside" class="img-fluid"
                                    loading="lazy">
                                <a href="assets/img/DSC_0439.jpg" class="gallery-overlay glightbox">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="gallery-item">
                                <img src="assets/img/DSC_4859.jpg" alt="Cozy Guest Room" class="img-fluid"
                                    loading="lazy">
                                <a href="assets/img/DSC_4859.jpg" class="gallery-overlay glightbox">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="gallery-item">
                                <img src="assets/img/DSC_4860.jpg" alt="Spa and Wellness Center"
                                    class="img-fluid" loading="lazy">
                                <a href="assets/img/DSC_4860.jpg" class="gallery-overlay glightbox">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="gallery-item">
                                <img src="assets/img/tarifario.png" alt="Conference Facilities"
                                    class="img-fluid" loading="lazy">
                                <a href="assets/img/tarifario.png" class="gallery-overlay glightbox">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="gallery-item">
                                <img src="assets/img/DSC_0418.jpg" alt="Cozy Guest Room" class="img-fluid"
                                    loading="lazy">
                                <a href="assets/img/DSC_0418.jpg" class="gallery-overlay glightbox">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="gallery-item">
                                <img src="assets/img/DSC_0425.jpg" alt="Spa and Wellness Center"
                                    class="img-fluid" loading="lazy">
                                <a href="assets/img/DSC_0425.jpg" class="gallery-overlay glightbox">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="gallery-item">
                                <img src="assets/img/DSC_0426.jpg" alt="Conference Facilities"
                                    class="img-fluid" loading="lazy">
                                <a href="assets/img/DSC_0426.jpg" class="gallery-overlay glightbox">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="gallery-item">
                                <img src="assets/img/DSC_0427.jpg" alt="Conference Facilities"
                                    class="img-fluid" loading="lazy">
                                <a href="assets/img/DSC_0427.jpg" class="gallery-overlay glightbox">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="gallery-item">
                                <img src="assets/img/DSC_0428.jpg" alt="Conference Facilities"
                                    class="img-fluid" loading="lazy">
                                <a href="assets/img/DSC_0428.jpg" class="gallery-overlay glightbox">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="gallery-item">
                                <img src="assets/img/DSC_0449.jpg" alt="Conference Facilities"
                                    class="img-fluid" loading="lazy">
                                <a href="assets/img/DSC_0449.jpg" class="gallery-overlay glightbox">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="gallery-item">
                                <img src="assets/img/DSC_4853.jpg" alt="Conference Facilities"
                                    class="img-fluid" loading="lazy">
                                <a href="assets/img/DSC_4853.jpg" class="gallery-overlay glightbox">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="gallery-item">
                                <img src="assets/img/DSC_4857.jpg" alt="Conference Facilities"
                                    class="img-fluid" loading="lazy">
                                <a href="assets/img/DSC_4857.jpg" class="gallery-overlay glightbox">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </section><!-- /Gallery Showcase Section -->

    </main>

    <footer id="footer" class="footer position-relative dark-background">

        <div class="footer-top">
            <div class="container">
                <div class="row gy-4">
                    <div class="col-lg-4 col-md-6 footer-about">
                        <a href="{{ route('login') }}" class="logo d-flex align-items-center">
                            <span class="sitename">Hotel Pinar del Río</span>
                        </a>
                        <div class="footer-contact pt-3">
                            <p>Sarmiento 284 esq. Rivadavia</p>
                            <p>Capilla del Monte, Córdoba, Argentina</p>
                            <p class="mt-3"><strong>WhatsApp:</strong> <span>+54 9 342 598-3527</span></p>
                            <p class="mt-3"><strong>WhatsApp:</strong> <span>+54 9 3448 61-8197</span></p>
                            <p class="mt-3"><strong>Teléfono:</strong> <span>+54 9 3548 489880</span></p>
                            <p class="mt-3"><strong>Email:</strong> <span>turismo@amep.org.ar</span></p>
                        </div>
                    </div>

                    <div class="col-lg-2 col-md-3 footer-links">
                        <h4>Enlaces de la Página</h4>
                        <ul>
                            <li><a href="#hotel-hero">Inicio</a></li>
                            <li><a href="#rooms-showcase">Habitaciones</a></li>
                            <li><a href="#amenities-cards">Servicios</a></li>
                            <li><a href="#location-section">Ubicación</a></li>
                            <li><a href="#contact-section">Contacto</a></li>
                        </ul>
                    </div>

                </div>
            </div>
        </div>

        <div class="copyright text-center">
            <div
                class="container d-flex flex-column flex-lg-row justify-content-center justify-content-lg-between align-items-center">

                <div class="d-flex flex-column align-items-center align-items-lg-start">
                    <div>
                        © Copyright 2026 - <strong><span>OM Computación</span></strong>. Todos los Derechos Reservados
                    </div>
                </div>

                <div class="social-links order-first order-lg-last mb-3 mb-lg-0">
                    <a href="https://www.facebook.com/amepmutual"><i class="bi bi-facebook"></i></a>
                    <a href="https://www.instagram.com/turismo.amep"><i class="bi bi-instagram"></i></a>
                </div>

            </div>
        </div>

    </footer>

    <!-- Scroll Top -->
    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>

    <!-- Preloader -->
    <div id="preloader"></div>

    <!-- Vendor JS Files -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/php-email-form/validate.js"></script>
    <script src="assets/vendor/aos/aos.js"></script>
    <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
    <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
    <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
    <script src="assets/vendor/imagesloaded/imagesloaded.pkgd.min.js"></script>
    <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>

    <!-- Main JS File -->
    <script src="assets/js/main.js"></script>

</body>

</html>
