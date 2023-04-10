history.pushState(null, null, location.href); 
history.back(); 
history.forward(); 
window.onpopstate = function () { 
    history.go(1); 
};
/*
history.pushState({
    page: 1
}, "Categoria", "#");
window.addEventListener("popstate", function(event) {
    history.pushState({
        page: 1
    }, "Categoria", "#");
});*/
