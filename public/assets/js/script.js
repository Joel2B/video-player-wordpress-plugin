const options = player.options;

const config = {
    layoutControls: {
        fillToContainer: true,
        primaryColor: options.primaryColor,
        autoPlay: options.autoPlay,
        loop: options.loop,
        playButtonShowing: true,
        playPauseAnimation: true,
        allowDownload: options.allowDownload,
        controlBar: {
            autoHide: true,
            autoHideTimeout: 3,
            animated: true,
        },
        timelinePreview: {
            file: options.thumbnails,
            type: 'VTT',
        },
        htmlOnPauseBlock: {
            html: options.htmlOnPauseBlock,
        },
        playerInitCallback: function () {
            const video = document.querySelector('video');
            const related = document.querySelector('#related-videos');
            video.parentNode.insertBefore(related, video.nextSibling);
        },
    },
    vastOptions: {
        vastTimeout: options.vastTimeout,
        adList: options.adList,
    },
};

if (options.logo) {
    config.layoutControls.logo = options.logo;
}

const playerInstance = fluidPlayer(player.id, config);

// hide / show related videos
const related = document.getElementById('related-videos');
playerInstance.on('ended', function () {
    related.style.display = 'flex';
});

playerInstance.on('play', function () {
    related.style.display = 'none';
});

playerInstance.on('seeked', function () {
    related.style.display = 'none';
});

playerInstance.on('theatreModeOn', function () {
    related.style.display = 'none';
});

// copy url of current post
const copyBtn = document.querySelector('.copy');
const url = document.querySelector('.url span');
copyBtn.addEventListener('click', async () => {
    try {
        await navigator.clipboard.writeText(url.textContent);
        copyBtn.textContent = 'Copied';
        setTimeout(() => {
            copyBtn.textContent = 'Copy';
        }, 3 * 1000);
    } catch (err) {
        console.log(err);
    }
});
