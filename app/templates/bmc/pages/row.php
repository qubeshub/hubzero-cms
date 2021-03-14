{xhub:include type="stylesheet" filename="pages/row.css"}

<div class="wrapper">
    <div class="header-logo">
        <div class="header-title">
            <img class="spotlight-logo" onerror="this.src='https://qubeshub.org/app/site/media/images/newsletters/ROW/community_spotlight.png'" alt="Logo: Resource of the Week" src="https://qubeshub.org/app/site/media/images/newsletters/ROW/community_spotlight.svg" />

            <div class="header-wrap">
                <h2>QUBES Community Spotlight</h2>
                <h3>Each month, we’ll be featuring an outstanding group, partner, resource, or member of our community.</h3>

                <p>QUBES is home to hundreds of communities focusing on particular aspects of quantitative biology education. In our monthly QUBES Community Spotlight, we celebrate and make visible the incredible work of our groups and partners, the vast library of open educational resources, and the individual members of our community that make it all possible.</p>

                <a href="https://qubeshub.org/community" class="btn">Learn About Our Community</a> <a class="btn" href="https://qubeshub.org/qubesresources">Explore QUBES Resources</a> <a class="btn" href="https://qubeshub.org/qubesresources#one">OER on QUBES</a>
            </div>


        </div>
    </div>

    <div class="most-recent">
        {xhub:module position="newsletter"}
        <h4>Latest</h4>
        {xhub:module position="rowRecent"}
    </div>
</div>

<div class="past-row">
    <h4>Community Spotlights</h4>
    {xhub:module position="rowPast"}
</div>