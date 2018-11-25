(function(){
    document.addEventListener("DOMContentLoaded",
        function n(){
            document.removeEventListener("DOMContentLoaded",n,!1);
            var m=document,g;g||(g={});
            var h=m.querySelectorAll("[data-gwd-motion-path-key]");
            if(0!=h.length)
            for(var k=0;k<h.length;k++){
                var a=h[k];
                var b=a.parentElement;
                if(b.hasAttribute("data-gwd-motion-path-node"))
                    for(;b.parentElement.hasAttribute("data-gwd-motion-path-node");)
                        b=b.parentElement;
                else b=null;
                if(!b){
                    b=a.getAttribute("data-gwd-motion-path-key");
                    b=!!g[b]||a.hasAttribute("data-gwd-has-tangent-following");
                    var f=a,c=m,p=b,l=f.getAttribute("data-gwd-motion-path-key"),d=c.createElement("div");
                    d.setAttribute("data-gwd-motion-path-node","x");d.className=l+"-anim-x";f.parentElement.insertBefore(d,f);
                    var e=c.createElement("div");
                    e.setAttribute("data-gwd-motion-path-node","y");
                    e.className=l+"-anim-y";
                    d.appendChild(e);
                    d=e;
                    p&&(c=c.createElement("div"),c.setAttribute("data-gwd-motion-path-node","theta"),c.className=l+"-anim-theta",e.appendChild(c),d=c);
                    d.appendChild(f);
                    b&&(a.setAttribute("data-gwd-mp-inline-ltwh",[a.style.left,a.style.top,a.style.width,a.style.height].join()),a.style.left="0px",a.style.top="0px",a.style.width="100%",a.style.height="100%")
                }
            }
        },!1
    );
}).call(this);

