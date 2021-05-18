{xhub:include type="stylesheet" filename="pages/news_and_activities.css"}

<main class="wrapper">
    <div class="header">
        <img alt="Logo - QUBES" src="/app/site/media/images/qubes_logo_web_200x200_transp.png" />
        <div class="header-title">
            <h1>News &amp; Activities</h1>
            <h4>The latest happenings on QUBES</h4>
        </div>
    </div>

    <div class="twitter-wrapper">
        <h2><a href="https://twitter.com/qubeshub">@qubeshub</a> Tweets</h2>
        <a class="twitter-timeline" data-link-color="#597F2F" data-chrome="noheader nofooter" data-tweet-limit="4" href="https://twitter.com/qubeshub?ref_src=twsrc%5Etfw">Tweets by qubeshub</a>
        <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
    </div>

    <div class="activities-wrapper">
        <div class="row">
            <h2>QUBES Community Spotlight</h2>

            <h6>See all <a href="/news/newsletter/row">community spotlights</a> and subscribe!</h6>
            {xhub:module position="newsRow"}
        </div>

        <div class="inner-wrap">
            <div class="blogs">
                <h2>Blogs</h2>

                <h6>View all <a href="/news/blog">blogs</a></h6>
                {xhub:module position="newsBlogs"}
            </div>

            <div class="newsletters">
                <h2>Newsletters</h2>
                <h6>Check out past <a href="/news/newsletter">newsletters</a></h6>
                {xhub:module position="newsNewsletters"}
            </div>
        </div>
    </div>
</main>