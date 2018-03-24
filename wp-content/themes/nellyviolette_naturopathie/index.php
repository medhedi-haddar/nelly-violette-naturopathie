<?php get_header() ; ?>

<main>
    <div class="container" id="intro">
        <div class="row">
            <div class="col-md-8 ml-auto mr-auto">
                <h2>écouter adapter accompagner</h2>
                <p>Trouvons votre équilibre, à votre rythme avec la naturopathie</p>
                <a href="" class="btn btn-l btn-cadre t-uppercase">
                    MON APPROCHE
                </a>
                <a href="" class="btn btn-l btn-cadre t-uppercase">
                    LA NATUROPATHIE
                </a>
            </div>
        </div>
    </div>
    <div id="approche-nelly" class="section">
        <div class="container">
            <div class="row">
                <h2 class="approche-title section-title">MON APPROCHE</h2>
                <div class="col-md-2 pl-0 pt-5" id="liste">
                    <ul>
                        <li>Aereis</li>
                        <li>Recteque</li>
                        <liConsiliis></li>
                        <li>Extimas</li>
                        <li>Liberis</li>
                    </ul>
                </div>
                <div class="col-md-5 pt-5">
                    <div class="block-text">
                        <span class="subtitle">Fuerit toto in consulatu sine provincia</span>
                        <p>Cui fuerit, antequam designatus est, decreta provincia. Sortietur an non ? Quo pervenire ante certam diem non licebit. ianuario,
                        Februario, provinciam non habebit; Kalendis ei denique Martiis nascetur.</p>
                    </div>
                    <div class="block-text">
                        <span class="subtitle">Fuerit toto in consulatu sine provincia</span>
                        <p>Cui fuerit, antequam designatus est, decreta provincia. Sortietur an non ? Quo pervenire ante certam diem non licebit. ianuario,
                        Februario, provinciam non habebit; Kalendis ei denique Martiis nascetur.</p>
                    </div>
                    <div id="point-fort">
                        <div class="items">
                            <div class="item col-md-6 pl-0">
                                <span class="numero">1</span>
                                <p>Ideo urbs venerabilis post superbas efferatarum gentium cervices oppressas latasque leges fundamenta libertatis.</p>
                            </div>
                            <div class="item col-md-6 ml-auto pl-0">
                                <span class="numero">2</span>
                                <p>Et retinacula sempiterna velut frugi parens et prudens et dives Caesaribus tamquam liberis suis regenda.</p>
                            </div>
                            <div class="item col-md-6 pl-0">
                                <span class="numero">3</span>
                                <p>Illud autem non dubitatur quod cum esset aliquando virtutum omnium domicilium Roma, ingenuos advenas plerique nobilium, ut Homerici bacarum suavitate Lotophagi, humanitatis multiformibus officiis retentabant.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 pr-0 ml-auto">
                    <div class="mini-aside">
                        <!-- conseil nelly -->
                            <div class="thumbnail bg-white ">
                                <div class="thumbnail-head">
                                    <img src="<?php echo esc_url(get_template_directory_uri()).'/assets/images/nelly-portrait-1.png';?>" alt="">
                                </div>
                                <div class="aside-slider slider">
                                    <div class="item  row justify-content-center align-items-center">
                                        <div class="col-md-8 m-auto">
                                            <span class="title">
                                                pour purifier vos intestins,
                                            </span>
                                        </div>
                                        <div class="col-md-10 m-auto">
                                            <p class="conseil">buver un verre d'eau chaude tous les matins</p>
                                        </div>
                                        <a href="" class="btn btn-m btn-red t-uppercase">En savoir plus</a>
                                    </div>
                                    <div class="item  row justify-content-center align-items-center">
                                        <div class="col-md-8 m-auto">
                                            <span class="title">
                                                pour purifier vos intestins,
                                            </span>
                                        </div>
                                        <div class="col-md-10 m-auto">
                                            <p class="conseil">buver un verre d'eau chaude tous les matins</p>
                                        </div>
                                        <a href="" class="btn btn-m btn-red t-uppercase">En savoir plus</a>
                                    </div>
                                    <div class="item  row justify-content-center align-items-center">
                                        <div class="col-md-8 m-auto">
                                            <span class="title">
                                                pour purifier vos intestins,
                                            </span>
                                        </div>
                                        <div class="col-md-10 m-auto">
                                            <p class="conseil">buver un verre d'eau chaude tous les matins</p>
                                        </div>
                                        <a href="" class="btn btn-m btn-red t-uppercase">En savoir plus</a>
                                    </div>
                                </div>
                            </div>
                                <!-- new blog -->
                            <div class="thumbnail bg-white">
                                <div class="item  row justify-content-center align-items-center">
                                    <?php 
                                    $args = array( 
                                    'numberposts' => '1', 
                                    );
                                    $recent_posts = wp_get_recent_posts( $args );
                                    foreach( $recent_posts as $recent ):

                                    $post_id        = $recent['ID'];
                                    $post_url       = get_permalink($recent['ID']);
                                     $post_title     = $recent['post_title'];
                                    $post_content   = $recent['post_content'];
                                    $post_thumbnail = get_the_post_thumbnail($recent['ID']);
                                    // image
                                    foreach (get_attached_media( 'image', $recent['ID'] ) as $key => $media) {
                                    $image_link = $media->guid;
                                    }
                                    endforeach;
                                    ?>
                                    <div class=" m-auto">
                                        <span class="title">
                                            <?php echo  $post_title; ?>
                                        </span>
                                    </div>
                                    <div class=" m-auto">
                                        <p class="conseil"><?php echo  $post_content; ?></p>
                                        <img src="<?php echo $image_link;?>" alt="">
                                    </div>
                                    <div class="footer">
                                        <a href="<?php echo $post_url ;?>" class="btn btn-m btn-green t-uppercase">En savoir plus</a>
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
            </div>
    </div>  
    <!-- NATUROPATHIE -->
    <div id="naturopathie" class="section">
        <div class="container">
            <h2 class="section-title">la naturopathie</h2>
            <div class="row">
                <div class="description col-md-6 mr-0">
                    <p>Vel contingit quod Vel contingit quod autem pecudum inertium bina contingit inertium contingit inertium autem</p>
                    
                    <p>Nihil morati post haec militares avidi saepe turbarum adorti sunt Montium primum, qui divertebat in proximo, levi corpore
                    senem atque morbosum, et hirsutis resticulis cruribus eius innexis nihil morati post</p>
                    <a href="" class="btn btn-cadre-dark">En savoir plus</a>
                </div>
                <div class="col-md-6 links">
                    <div class="row flex-row justify-content-around">
                    <div class="item row flex-column align-items-center" id="techniques">
                        <span>10</span>
                        <span>techniques</span>
                    </div>
                    <div class="item row flex-column align-items-center" id="principes">
                        <span>5</span>
                        <span>principes</span>
                    </div>
                    <div class="item row flex-column align-items-center" id="profils">
                        <span>3</span>
                        <span>profils</span>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="secction" id="besoins-specifique">
        <div class="container title ">
            <div class="row flex-column text-center">
                <span>Pour chacun</span>
                <span>des besoins spécifiques et adaptés</span>
            </div>
        </div>
        
        <div class="container mt-10">
            <div class="row">
            <div class="col-md-3 text-center" id="requilibre">
            <div class="item" >
                <div class="item-title">
                    <span>rééquilibrer l'alimentation de théo,</span>
                </div>
                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Pariatur qui esse provident iste quasi sunt architecto,
                    nesciunt vitae quisquam nisi. Tenetur voluptates delectus, commodi dolor culpa beatae amet quae provident?</p>
                <div  class="text-center mt-6">
                    <a  class="pour-quoi" data-toggle="collapse" href="#pq-content" role="button" aria-expanded="false" aria-controls="collapseExample">
                        pour quoi ?</a>
                    <p  class="collapse" id="pq-content" >Lorem ipsum dolor sit, amet consectetur adipisicing elit. Amet aperiam voluptatibus, 
                        quas error dolorem dolore! Enim vitae officiis blanditiis voluptatem sed, officia similique id quaerat totam ullam magnam quidem optio!</p>
                </div>
                <div  class="text-center mt-6">
                    <a  class="comment" id="comment" data-toggle="collapse" href="#comment-content" role="button" aria-expanded="false" aria-controls="collapseExample">
                        comment ?</a>
                    <p class="collapse" id="comment-content" >Lorem ipsum dolor sit, amet consectetur adipisicing elit. Amet aperiam voluptatibus, quas error dolorem dolore! Enim vitae
                        officiis blanditiis voluptatem sed, officia similique id quaerat totam ullam magnam quidem optio!</p>
                </div>
                <a href="" class="btn btn-cadre-dark"> En savoir plus</a>
            </div>
            </div>
            <div class="col-md-3 text-center" id="combattre">
                <div class="item">
                <div class="item-title">
                    <span>rééquilibrer l'alimentation de théo,</span>
                </div>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Pariatur qui esse provident iste quasi sunt architecto, nesciunt
                        vitae quisquam nisi. Tenetur voluptates delectus, commodi dolor culpa beatae amet quae provident?</p>
                    <div class="text-center  mt-6">
                        <a class="pour-quoi" data-toggle="collapse" href="#pq-combattre-content" role="button" aria-expanded="false" aria-controls="collapseExample">
                            pour quoi ?</a>
                        <p class="collapse" id="pq-combattre-content">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Amet aperiam voluptatibus, quas error dolorem dolore! Enim
                            vitae officiis blanditiis voluptatem sed, officia similique id quaerat totam ullam magnam quidem optio!</p>
                    </div>
                    <div class="text-center  mt-6">
                        <a class="comment" data-toggle="collapse" href="#comment-combattre-content" role="button" aria-expanded="false" aria-controls="collapseExample">
                            comment ?</a>
                        <p class="collapse" id="comment-combattre-content">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Amet aperiam voluptatibus, quas error dolorem dolore! Enim
                            vitae officiis blanditiis voluptatem sed, officia similique id quaerat totam ullam magnam quidem optio!</p>
                    </div>
                    <a href="" class="btn btn-cadre-dark"> En savoir plus</a>
                </div>
            </div>
            <div class="col-md-3 text-center"  id="sante">
                <div class="item">
                <div class="item-title">
                    <span>rééquilibrer l'alimentation de théo,</span>
                </div>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Pariatur qui esse provident iste quasi sunt architecto, nesciunt
                        vitae quisquam nisi. Tenetur voluptates delectus, commodi dolor culpa beatae amet quae provident?</p>
                    <div class="text-center  mt-6">
                        <a class="pour-quoi" data-toggle="collapse" href="#pq-sante-content" role="button" aria-expanded="false" aria-controls="collapseExample">
                            pour quoi ?</a>
                        <p class="collapse" id="pq-sante-content">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Amet aperiam voluptatibus, quas error dolorem dolore! Enim
                            vitae officiis blanditiis voluptatem sed, officia similique id quaerat totam ullam magnam quidem optio!</p>
                    </div>
                    <div class="text-center  mt-6">
                        <a class="comment" data-toggle="collapse" href="#comment-sante-content" role="button" aria-expanded="false" aria-controls="collapseExample">
                            comment ?</a>
                        <p class="collapse" id="comment-sante-content">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Amet aperiam voluptatibus, quas error dolorem dolore! Enim
                            vitae officiis blanditiis voluptatem sed, officia similique id quaerat totam ullam magnam quidem optio!</p>
                    </div>
                    <a href="" class="btn btn-cadre-dark"> En savoir plus</a>
                </div>
            </div>
            <div class="col-md-3 text-center" id="traitement">
                <div class="item">
                <div class="item-title">
                    <span>rééquilibrer l'alimentation de théo,</span>
                </div>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Pariatur qui esse provident iste quasi sunt architecto, nesciunt
                        vitae quisquam nisi. Tenetur voluptates delectus, commodi dolor culpa beatae amet quae provident?</p>
                    <div class="text-center  mt-6">
                        <a class="pour-quoi" data-toggle="collapse" href="#pq-traitement-content" role="button" aria-expanded="false" aria-controls="collapseExample">
                            pour quoi ?</a>
                        <p class="collapse" id="pq-traitement-content">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Amet aperiam voluptatibus, quas error dolorem dolore! Enim
                            vitae officiis blanditiis voluptatem sed, officia similique id quaerat totam ullam magnam quidem optio!</p>
                    </div>
                    <div class="text-center  mt-6">
                        <a class="comment" id="comment-traitement-button" data-toggle="collapse" href="#comment-traitement-content" role="button" aria-expanded="false" aria-controls="collapseExample">
                            comment ?</a>
                        <p class="collapse" id="comment-traitement-content">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Amet aperiam voluptatibus, quas error dolorem dolore! Enim
                            vitae officiis blanditiis voluptatem sed, officia similique id quaerat totam ullam magnam quidem optio!</p>
                    </div>
                    <a href="" class="btn btn-cadre-dark"> En savoir plus</a>
                </div>
            </div>
        </div>
        </div>
    </div>
    <!-- CONTACT -->
    <div class="contact">
            <div class="content">
                <div class="container">
                    
                    <div class="title">
                        <h2>Contact</h2>
                    </div>
                    <div class="row  justify-content-between">
                        <a href="" id="telephone">07 73 85 51 21</a>
                        <a href="" id="email">contact@nellyviolette-naturopathe.fr</a>
                        <a href="" id="rdv">Prendre rendez-vous</a>
                        <a href="" id="question">Poser une question</a>
                    </div>
                </div>
            </div>
          </div>
</main>

<?php
    get_footer();
?>
