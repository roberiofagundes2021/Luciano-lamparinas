/* ------------------------------------------------------------------------------
 *
 *  # Form validation
 *
 *  Demo JS code for form_validation.html page
 *
 * ---------------------------------------------------------------------------- */


// Setup modul


// Initialize module
// ------------------------------

document.addEventListener('DOMContentLoaded', function() {
    $( "#formProduto" ).validate({
        debug: true,
        rules: {
           inputNome: {
               required: true,
               email: true,
            },
            campo2:{
               minlength: 3,
               maxlength: 4,
              // ou
              rangelength: [3, 4] //Realiza a mesma coisa dos anteriores
            },
            campo3:{
               min: 10,
               max: 15,
              // ou
               range: [10, 15] //Realiza a mesma coisa dos anteriores
            success: function(label) {
                label.addClass('validation-valid-label').text('Sucesso.'); // remove to hide Success message
            },
            campo4:{
               accept: "audio/*"
            },
            telefone_pessoal: {
            require_from_group: [1, ".grupo_telefone"]
        },
     telefone_casa: {
       require_from_group: [1, ".grupo_telefone"]
     },
     telefone_trabalho: {
       require_from_group: [1, ".grupo_telefone"]
     }    
   },
   messages:{
         //exemplo
      inputNome: {
       required: "Mensagem customizada: Informe um tipo de arquivo válido!"
                  }
   }
 });
});
