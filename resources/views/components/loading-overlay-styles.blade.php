<style>
#loader.kx-loader{display:none;position:fixed;inset:0;z-index:100000;transform:none;pointer-events:auto;background:rgba(4,10,7,.58);backdrop-filter:blur(7px);-webkit-backdrop-filter:blur(7px)}
.kx-loader__panel{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:min(290px,calc(100vw - 36px));padding:25px 22px 21px;border:1px solid rgba(74,222,128,.28);border-radius:24px;background:linear-gradient(145deg,rgba(18,31,25,.97),rgba(9,17,14,.98));box-shadow:0 24px 80px rgba(0,0,0,.45),inset 0 1px rgba(255,255,255,.07);color:#eef8f0;text-align:center;overflow:hidden}
.kx-loader__panel:before{content:"";position:absolute;width:170px;height:110px;left:50%;top:-75px;transform:translateX(-50%);border-radius:50%;background:rgba(0,204,80,.2);filter:blur(22px)}
.kx-loader__brand{position:relative;display:flex;justify-content:center;align-items:center;gap:10px;margin-bottom:17px;font-size:14px;font-weight:800;letter-spacing:.01em}
.kx-loader__brand img{width:34px;height:34px;object-fit:cover;border-radius:11px;box-shadow:0 0 18px rgba(0,204,80,.22)}
.kx-loader__classic,.kx-loader__festive{position:relative;display:none}
#loader.kx-loader--classic .kx-loader__classic,#loader.kx-loader--festive .kx-loader__festive{display:block}
.kx-loader__ring{width:42px;height:42px;margin:0 auto 13px;border-radius:50%;border:3px solid rgba(255,255,255,.1);border-top-color:#35e878;border-right-color:#0cae45;animation:kx-loader-spin .85s linear infinite}
.kx-loader__classic .kx-loader__ring{width:30px;height:30px;border-width:3px;margin-bottom:10px}
.kx-loader__title{font-size:.95rem;font-weight:750;line-height:1.35;margin:0;color:#f5fff7}
.kx-loader__hint{font-size:.76rem;color:#9caea2;margin:5px 0 0}
/* A layered tree, glass-like baubles, and soft snowfall create the seasonal 3D scene. */
.kx-festive-scene{height:118px;position:relative;margin:0 auto 7px;perspective:450px;overflow:hidden;border-radius:17px;background:radial-gradient(ellipse at 50% 90%,rgba(49,207,105,.2),transparent 58%)}
.kx-festive-tree{position:absolute;left:50%;bottom:14px;width:87px;height:97px;transform:translateX(-50%) rotateX(7deg);filter:drop-shadow(0 10px 12px rgba(0,0,0,.35))}
.kx-festive-tree i{position:absolute;left:50%;width:0;height:0;transform:translateX(-50%);border-left:var(--w) solid transparent;border-right:var(--w) solid transparent;border-bottom:var(--h) solid var(--c);filter:drop-shadow(0 4px 7px rgba(0,0,0,.18))}
.kx-festive-tree i:nth-child(1){--w:18px;--h:29px;--c:#49df88;top:5px}.kx-festive-tree i:nth-child(2){--w:29px;--h:37px;--c:#179e5a;top:25px}.kx-festive-tree i:nth-child(3){--w:40px;--h:43px;--c:#087345;top:48px}
.kx-festive-tree:after{content:"";position:absolute;left:38px;bottom:0;width:12px;height:19px;border-radius:2px;background:linear-gradient(90deg,#71431e,#bb7d3b,#71431e)}
.kx-festive-star{position:absolute;top:2px;left:50%;z-index:2;transform:translateX(-50%);color:#ffe8a1;font-size:19px;text-shadow:0 0 13px #ffc947;animation:kx-twinkle 1.8s ease-in-out infinite}
.kx-bauble{position:absolute;z-index:3;width:11px;height:11px;border-radius:50%;background:radial-gradient(circle at 30% 25%,#fff6d6,#ffcb5c 32%,#b76b18 78%);box-shadow:0 0 10px rgba(255,192,76,.45);animation:kx-float 2.7s ease-in-out infinite}
.kx-bauble:nth-child(3){left:calc(50% - 19px);top:55px}.kx-bauble:nth-child(4){left:calc(50% + 13px);top:75px;background:radial-gradient(circle at 30% 25%,#e8ffff,#70dcff 35%,#217da0 80%);animation-delay:.6s}.kx-bauble:nth-child(5){left:calc(50% - 31px);top:91px;background:radial-gradient(circle at 30% 25%,#fff1f1,#ff7787 35%,#982f50 80%);animation-delay:1s}
.kx-snow{position:absolute;top:-10px;color:#ecfff3;opacity:.75;font-size:8px;animation:kx-snowfall 3.8s linear infinite}.kx-snow:nth-child(1){left:14%;animation-delay:.2s}.kx-snow:nth-child(2){left:32%;animation-delay:1.6s}.kx-snow:nth-child(3){left:70%;animation-delay:.8s}.kx-snow:nth-child(4){left:87%;animation-delay:2.2s}
.kx-festive-progress{height:4px;border-radius:9px;background:rgba(255,255,255,.1);margin:13px 3px 0;overflow:hidden}.kx-festive-progress i{display:block;height:100%;width:38%;border-radius:9px;background:linear-gradient(90deg,#35e878,#ffe19b);box-shadow:0 0 10px rgba(255,222,139,.6);animation:kx-progress 1.8s ease-in-out infinite alternate}
@keyframes kx-loader-spin{to{transform:rotate(360deg)}}@keyframes kx-float{50%{transform:translateY(-5px) scale(1.08)}}@keyframes kx-twinkle{50%{filter:brightness(1.4);transform:translateX(-50%) scale(1.18)}}@keyframes kx-snowfall{to{transform:translateY(140px) translateX(12px);opacity:0}}@keyframes kx-progress{to{width:92%}}
@media(prefers-reduced-motion:reduce){.kx-loader__ring,.kx-bauble,.kx-festive-star,.kx-snow,.kx-festive-progress i{animation-duration:3s}}
body.kx-light #loader.kx-loader{background:rgba(17,28,20,.34)}
body.kx-light .kx-loader__panel{background:linear-gradient(145deg,rgba(255,255,255,.98),rgba(239,248,241,.99));color:#132219;border-color:rgba(0,150,60,.2);box-shadow:0 24px 80px rgba(16,40,24,.2)}
body.kx-light .kx-loader__title{color:#132219}body.kx-light .kx-loader__hint{color:#5d7062}
</style>
