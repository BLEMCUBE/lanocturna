<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import prueba1 from '/public/images/prueba1.png';
import { onMounted,ref } from 'vue';
import * as PIXI from 'pixi.js';
// typical import
//import gsap from "gsap";

// get other plugins:
//import ScrollTrigger from "gsap/ScrollTrigger";
import Flip from "gsap/Flip";
//import Draggable from "gsap/Draggable";

// or all tools are exported from the "all" file (excluding members-only plugins):
import { gsap, ScrollTrigger, Draggable, MotionPathPlugin } from "gsap/all";

// don't forget to register plugins
gsap.registerPlugin(ScrollTrigger, Draggable, Flip, MotionPathPlugin);
const visibleLeft = ref(false);

onMounted(() => {
    document.getElementById('main-scene').appendChild(app.view);
gsap.set('.pin',{opacity:0});
})

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});




const app = new PIXI.Application({
    //width: 800,
    //height: 600,
    backgroundColor: 0x000000,
    backgroundAlpha: 0,
    antialias: true,
    autoDensity: true,
    resolution: 2,
    resizeTo: window
});
var stage = app.stage;
//renderIsometricTile(stage, 20, 20, 50, 5, 0x989865, 0x767643, 0x545421);
//renderIsometricTile(stage, 60, 40, 50, 5, 0x2A63FF, 0x0841DD, 0x0620AA);
//renderIsometricTile(stage, 148, 39, 50, 5, 0x59CD90, 0x37AB70, 0x257750);
//renderIsometricTile(stage, 200, 200, 150, 10, 0x3D3D8B, 0x1B1B6A, 0x1B1B48);
renderIsometricTile(stage, 100, app.screen.height*0.1, app.screen.height, 10, 0xFFD23F, 0xDDB01D, 0xBB7A0A);
function renderIsometricTile(stage, x, y, size, height, topColor, leftColor, rightColor) {
  var topSide = new PIXI.Graphics();

  topSide.beginFill(topColor);
  topSide.drawRect(0, 0, size, size);
  topSide.endFill();
  topSide.setTransform(x, y + size * 0.5, 1, 1, 0, 1.1, -0.5, 0, 0);

  var leftSide = new PIXI.Graphics();

  leftSide.beginFill(leftColor);
  leftSide.drawRect(0, 0, height, size);
  leftSide.endFill();
  leftSide.setTransform(x, y + size * 0.5, 1, 1, 0, 1.1, 1.57, 0, 0);

  var rightSide = new PIXI.Graphics();

  rightSide.beginFill(rightColor);
  rightSide.drawRect(0, 0, size, height);
  rightSide.endFill();
  rightSide.setTransform(x, y + size * 0.5, 1, 1, 0, -0.0, -0.5, -(size + (size * 0.015)), -(size - (size * 0.06)));

  stage.addChild(topSide);
  stage.addChild(leftSide);
  stage.addChild(rightSide);
}
function onClick()
{
    //sprite.scale.x *= 1.25;
    //sprite.scale.y *= 1.25;
    visibleLeft.value=true;

    //alert('¡d');
}
const onButtonOver=(event,flower)=>{
    //console.log('global',event.global);
    console.log('d',flower.x);
    let x=flower.x;
    let y=flower.y;
    //gsap.from('.pin',{y:y-100,x:x,opacity:0, ease:'back'});
    gsap.set('.pin',{y:y-100,x:x,opacity:0});
    gsap.to('.pin',{duration:0.5,y:y-50,opacity:1,x:x,ease:'back'});
}
const onButtonOut=(event,flower)=>{
    //console.log('global',event.global);
    console.log('d',flower.x);
    let x=flower.x;
    let y=flower.y;
    //gsap.from('.pin',{duration:1,y:y-50,opacity:1,x:x});

    gsap.to('.pin',{y:y-100,x:x,opacity:0, ease:'back'});
}
// Add the assets to load
PIXI.Assets.add('flowerTop', prueba1,'png');
PIXI.Assets.add('eggHead', 'https://pixijs.com/assets/eggHead.png');
// Opt-in to interactivity

// Load the assets and get a resolved promise once both are loaded
const texturesPromise = PIXI.Assets.load(['flowerTop', 'eggHead']); // => Promise<{flowerTop: Texture, eggHead: Texture}>
PIXI.settings.SCALE_MODE = PIXI.SCALE_MODES.NEAREST;
// When the promise resolves, we have the texture!
texturesPromise.then((textures) =>
{
    // create a new Sprite from the resolved loaded Textures

    const flower = PIXI.Sprite.from(textures.flowerTop);

    flower.anchor.set(0.5);
    flower.x = app.screen.width *0.5;
    flower.y = app.screen.height / 2;
    flower.scale.set(0.25)
    //flower.width=app.screen.width /4;
    //flower.height=app.screen.height /4;
    app.stage.addChild(flower);
    flower.eventMode = 'static';
    //flower.eventMode = 'dynamic';
// Shows hand cursor
flower.cursor = 'pointer';

flower.on('pointerdown', onClick)  .on('pointerout', (event)=> onButtonOut(event,flower))

.on('pointerover',(event)=> onButtonOver(event,flower))
/*    const egg = PIXI.Sprite.from(textures.eggHead);

    egg.anchor.set(0.5);
    egg.x = app.screen.width * 0.75;
    egg.y = app.screen.height / 2;
    app.stage.addChild(egg);*/
});
//var myView = document.getElementById('myCanvas');


//var renderer = PIXI.autoDetectRenderer(400, 400, myView);

//const app = new PIXI.Application({  backgroundAlpha: 0, resizeTo: window });

//document.body.appendChild(app.view);


</script>

<template>



        <section class="">


            <div class="pin z-50">
                </div>
            <div class="w-auto" id="main-scene">

                <Sidebar v-model:visible="visibleLeft" :pt="{
                    root:{class:'w-3/5'}
                }">
            <h2>Ing. Oscar Jimmy</h2>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
        </Sidebar>
            </div>
        </section>



</template>

<style type="text/css" scoped>
 .pin {
        /*display: block;
        top: -15px;
        left: 24px;*/
        height: 27px;
        width: 22px;
        background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADgAAABECAMAAADjuAaWAAABHVBMVEUAAACmg3GkhHKihHOjhHKihHOihHOng3CihHOfgnGTeWqbf2//TBKFbWCGb2L/f0qJcWSIcWT/f0qEbmGggnH/TxX/WB7/YCeFb2H/f0qHb2GGb2J/al7/dD7/URf/Uxr/f0r/f0r/f0pvXlX2eEf/f0r/f0r/f0r8Xyf/f0r/YSr/f0rxd0f/f0r/Uhn/WB7/f0r/WR/6WyT/XCT/XST/f0r3fkv/Xyb/YCf/f0r/f0r/f0r/Yir/f0r8f0v/f0r/Vhz/Vhz/WB7/WyP/f0r2fkv/Xyb/f0r8YCj/YCfwfk//f0r/Yir/cTv/SA3/f0r/PAD/Uhj/QgT/Rgv/PgH/Qwf/fEb/Zy3/WR3/Rwr/dT7/YSb/eUL/bDT/UBW3DuFJAAAATnRSTlMAAgQSBw4JCxYUIBv9ODLrKxj8RRj52Vg+MSYjHxj07LmknSYl283DiEZCPxMN797V0MWimZKHdnBmXk8w9ubl49/ctKyjfHRrZFk5LSH9dcKPAAACuUlEQVRIx63V+VPaQBTA8U2ACBLOWm4EKSJVaL1btfd9kgShirb+/39GXza7POFll0yn31+ccfKZt2yWhZGa551eruiN7M3S8QsWNeOiXXT8vBGv1Tcjucf7YAQUPXy2mg23QCCUPW2ucN1th0I+dFfrqjmHQJFd1s0DR6GU6plDXOevq7uxC02ml7japgrivtxO3Hlj3CHVe5Ds98hdaCZl+Fsx9qUbu4tNLuVijTB4IT/ezCVJ2Q+DbQGvCcPP2WK0ZjFwNy5GR/6g8BwHakYeU9gRcOyGJmCJwh1cqWatmxSK0/ZHAacBtCkUe3OlhjwKnX+FL8lSI8JtcW4UcCYOHYUfnKBZqJuIgW8o/CzgrfYAvKewKs/4WHfkyhQaObKvdGtsk6nPHJ5WegmUtBeA45GdkX0Kg2YOrxw6j2cPWVgnzrwbj9wbfgfE4L7i/Xh9N5mMp8hwT0k9dPRCxu8U7WwFPFLBtZwW2gOm6lQLD5myZlEHdb/pWxr4jmmqamCZ6WorYYtp+66EX5m+HQV8wla0p4DP1YSO9MjAiCM9MlBbOwS2WISqIbDMorRF4FsWqW5xGe6yaJ0swQMWsUFuAdo/WdTOFuARi5zZuwc34wYUQRnQ3j34xYQMnh6Zfp05LFmWFedJH+7MeNyCBq8FtLuJRGLND/7L9bL1R/kKHoFHY98E7BegVCoVS/gBl5NxlmCAYrFUofCRw9IjqN5oNApgIW4tPhfX6DvOkslCo1E7feXZh5lMJpvN5mu1enojmUwGlC8ZiJxnBS4FD6TrtXw+m6k84FUy2Xx+ncsUl3ImhRvrgcxUKpVgZj1NoJR8pXypvkxD6/MCRZeKklMI9jGJ8Um4OcCE8+XS6yABArXE0PK99VuDEjJ5ACxfIVqwXAvPg7/xIHlm1edVcswQUUU1jf2//gK1k+2vDRDbvwAAAABJRU5ErkJggg==);
        background-size: 100%;
        background-repeat: no-repeat;
        /*position: absolute;*/
        z-index: 3
    }
</style>
