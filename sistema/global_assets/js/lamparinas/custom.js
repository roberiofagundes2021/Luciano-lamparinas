/* Função responsavel pelos alertas do sistema */
function alerta(titulo, msg, tipo, modal) {
	var opts = {
		title: '',
		text: '',
		type: '',
		icon: '',
	};

	opts.title = titulo;
	opts.text = msg;
	opts.type = tipo;
	//opts.desktop = {desktop: true}
	opts.addclass = 'stack-modal';
	opts.stack = { dir1: 'down', dir2: 'right', modal: false };

	switch (tipo) {
		case 'success':
			opts.icon = 'icon-checkmark3';
			break;
		case 'error':
			opts.icon = 'icon-blocked';
			break;
		case 'info':
			opts.icon = 'icon-info22';
			break;
	}

	//console.log(opts);
	//PNotify.desktop.permission();

	new PNotify(opts);
}

/* Confirma exclusão antes de excluir um registro */
function confirmaExclusao(form, texto, acao, acaoComplete) {
	new PNotify({
		title: 'Confirmação',
		text: texto,
		icon: 'icon-question4',
		hide: false,
		confirm: {
			confirm: true,
			buttons: [
				{
					text: 'Sim',
					primary: true,
					click: function (notice) {
						form.action = acao;
						form.submit();
					},
				},
				{
					text: 'Não',
					click: function (notice) {
						notice.remove();
					},
				},
			],
		},
		buttons: {
			closer: false,
			sticker: false,
		},
		history: {
			history: false,
		},
		addclass: 'stack-modal',
		stack: { dir1: 'down', dir2: 'right', modal: false },
	})
		.get()
		.on('pnotify.confirm', function () {
			form.action = acao;
			form.submit();
		})
		.get()
		.on('pnotify.cancel', function () {
			return false;
		});
}

function confirmaExclusaoAjax(url, texto, tipoRequest, id, acaoSuccess) {
	new PNotify({
		title: 'Confirmação',
		text: texto,
		icon: 'icon-question4',
		hide: false,
		confirm: {
			confirm: true,
			buttons: [
				{
					text: 'Sim',
					primary: true,
					click: function (notice) {
						$.ajax({
							type: 'POST',
							url: url,
							dataType: 'json',
							data: {
								'tipoRequest': tipoRequest,
								'id': id
							},
							success: function(response) {
								if(response.status  == 'success'){
									alerta(response.titulo, response.menssagem, response.status)
									acaoSuccess()
								} else {
									alerta(response.titulo, response.menssagem, response.status)
								}
							}
						});
						notice.remove();
					},
				},
				{
					text: 'Não',
					click: function (notice) {
						notice.remove();
					},
				},
			],
		},
		buttons: {
			closer: false,
			sticker: false,
		},
		history: {
			history: false,
		},
		addclass: 'stack-modal',
		stack: { dir1: 'down', dir2: 'right', modal: false },
	})
}

function confirmaReset(form, texto, acao, id) {
	new PNotify({
		title: 'Confirmação',
		text: texto,
		icon: 'icon-question4',
		hide: false,
		confirm: {
			confirm: true,
			buttons: [
				{
					text: 'Sim',
					primary: true,
					click: function (notice) {
						form.action = acao;
						$("#inputTypeRequest").val("reset");
						if(Array.isArray(id)){
							id.forEach(element => {
								$("#"+element).val(null);
							})
						}else{
							$("#"+id).val(null);
						}
						form.submit();
					},
				},
				{
					text: 'Não',
					click: function (notice) {
						notice.remove();
					},
				},
			],
		},
		buttons: {
			closer: false,
			sticker: false,
		},
		history: {
			history: false,
		},
		addclass: 'stack-modal',
		stack: { dir1: 'down', dir2: 'right', modal: false },
	})
		.get()
		.on('pnotify.confirm', function () {
			form.action = acao;
			form.submit();
		})
		.get()
		.on('pnotify.cancel', function () {
			return false;
		});
}

function moeda(z) {
	v = z.value;
	v = v.replace(/\D/g, ''); //Permite digitar apenas números
	v = v.replace(/[0-9]{12}/, 'inválido'); //limita pra máximo 999.999.999,99
	v = v.replace(/(\d)(\d{8})$/, '$1.$2'); //coloca o ponto dos milhões
	v = v.replace(/(\d)(\d{5})$/, '$1.$2'); //coloca o ponto dos milhares
	//v = v.replace(/(\d{1})(\d{1,2})$/,"$1.$2") //coloca ponto antes dos últimos 2 digitos
	v = v.replace(/(\d{1})(\d{1,2})$/, '$1,$2'); //coloca virgula antes dos últimos 2 digitos
	z.value = v;
}

function moedatofloat(num) {
	if (num === '') {
		num = 0;
	} else {
		num = num.replace('.', '');
		num = num.replace(',', '.');
		num = parseFloat(num);
	}
	return num;
}

function float2moeda(num) {
	x = 0;

	if (num < 0) {
		num = Math.abs(num);
		x = 1;
	}
	if (isNaN(num)) num = '0';
	cents = Math.floor((num * 100 + 0.5) % 100);

	num = Math.floor((num * 100 + 0.5) / 100).toString();

	if (cents < 10) cents = '0' + cents;
	for (var i = 0; i < Math.floor((num.length - (1 + i)) / 3); i++)
		num =
			num.substring(0, num.length - (4 * i + 3)) +
			'.' +
			num.substring(num.length - (4 * i + 3));
	ret = num + ',' + cents;
	if (x == 1) ret = ' - ' + ret;

	return ret;
}

/* Só aceita numeros */
function onlynumber(evt) {
	var theEvent = evt || window.event;
	var key = theEvent.keyCode || theEvent.which;
	key = String.fromCharCode(key);
	//var regex = /^[0-9.,]+$/;
	var regex = /^[0-9]+$/;
	if (!regex.test(key)) {
		theEvent.returnValue = false;
		if (theEvent.preventDefault) theEvent.preventDefault();
	}
}

function validaCPF(strCPF) {
    var Soma;
    var Resto;
    Soma = 0;
    let numerosUnicos = [...new Set(strCPF)];
    if(numerosUnicos.length <=1){
        return false;
    }

    for (i = 1; i <= 9; i++) Soma = Soma + parseInt(strCPF.substring(i - 1, i)) * (11 - i);
    Resto = (Soma * 10) % 11;

    if ((Resto == 10) || (Resto == 11)) Resto = 0;
    if (Resto != parseInt(strCPF.substring(9, 10))) return false;

    Soma = 0;
    for (i = 1; i <= 10; i++) Soma = Soma + parseInt(strCPF.substring(i - 1, i)) * (12 - i);
    Resto = (Soma * 10) % 11;

    if ((Resto == 10) || (Resto == 11)) Resto = 0;
    if (Resto != parseInt(strCPF.substring(10, 11))) return false;
    return true;
}

function validarCNPJ(cnpj) {
 
	cnpj = cnpj.replace(/[^\d]+/g,'');

	if(cnpj == '') return false;
	
	if (cnpj.length != 14)
		return false;

	let numerosUnicosCnpj = [...new Set(cnpj)];
    if(numerosUnicosCnpj.length <=1){
    return false;
    }
	
	// Valida DVs
	tamanho = cnpj.length - 2
	numeros = cnpj.substring(0,tamanho);
	digitos = cnpj.substring(tamanho);
	soma = 0;
	pos = tamanho - 7;
	for (i = tamanho; i >= 1; i--) {
	soma += numeros.charAt(tamanho - i) * pos--;
	if (pos < 2)
			pos = 9;
	}
	resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
	if (resultado != digitos.charAt(0))
		return false;
		
	tamanho = tamanho + 1;
	numeros = cnpj.substring(0,tamanho);
	soma = 0;
	pos = tamanho - 7;
	for (i = tamanho; i >= 1; i--) {
	soma += numeros.charAt(tamanho - i) * pos--;
	if (pos < 2)
			pos = 9;
	}
	resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
	if (resultado != digitos.charAt(1))
		return false;
			
	return true;
	
}	

//Esta função será executada quando o campo cep perder o foco.
function ValidaEPreencheCEP(
	idCampoCEP,
	idCampoEndereco,
	idCampoBairro,
	idCampoCidade,
	idCampoEstado
	) {

	//Nova variável "cep" somente com dígitos.
	var cep = $(`#${idCampoCEP}`).val().replace(/\D/g, '');

	//Verifica se campo cep possui valor informado.
	if (cep != "") {

		//Expressão regular para validar o CEP.
		var validacep = /^[0-9]{8}$/;

		//Valida o formato do CEP.
		if (validacep.test(cep)) {
			
			//Preenche os campos com "..." enquanto consulta webservice.
			$(`#${idCampoEndereco}`).val("...");
			$(`#${idCampoBairro}`).val("...");
			$(`#${idCampoCidade}`).val("...");
			$(`#${idCampoEstado}`).val("...");

			//Consulta o webservice viacep.com.br/
			$.getJSON("https://viacep.com.br/ws/" + cep + "/json/?callback=?", function(dados) {
				if (!("erro" in dados)) {
					//Atualiza os campos com os valores da consulta.
					$(`#${idCampoEndereco}`).val(dados.logradouro);
					$(`#${idCampoBairro}`).val(dados.bairro);
					$(`#${idCampoCidade}`).val(dados.localidade);					
					$(`#${idCampoEstado}`).val(dados.uf);
					$(`#${idCampoEstado}`).children("option").each(function(index, item){									
						if($(item).val().toUpperCase() == dados.uf.toUpperCase()){
							$(item).change()
						}
					})
				}
				else {
					//CEP pesquisado não foi encontrado.
					limpa_formulário_cep();
					alerta("Erro", "CEP não encontrado.", "erro");
				}
			});
		}
		else {
			//cep é inválido.
			$(`#${idCampoCEP}`).val("");
			limpa_formulário_cep();
			alerta("Erro", "Formato de CEP inválido.", "erro");
		}
	}
	else {
		//cep sem valor, limpa formulário.
		limpa_formulário_cep();
	}
}

function WebSocketConnect(unidade, empresa){
	/*
		Ao enviar dados o servidor esta esperando um json com os seguintes campos:
		->type: tipo de requisição, tratase de um identificador para saber como o servidor deverá tratar os dados, não pode ser nulo;
		->cor: esse campo pode ser nulo, serve para, caso queira exibir uma menssagem em tempo real, seja possivel alterar a cor dessa menssagem;
		->timeout: esse campo pode ser nulo, serve para, caso queira exibir uma menssagem em tempo real, seja possivel controla o tempo que ficará na tela;
		->menssage: esse campo pode ser nulo, serve para, caso queira exibir uma menssagem em tempo real, seja possivel customisar essa menssagem;
	
	*/
	// OBS.: criar uma maneira de não fazer a conexão sempre que recarregar a página;

	let habilitado = false
	var socket = {
		'onerror': (event)=>{},
		'onclose': (event)=>{},
		'onopen': (event)=>{},
		'sendMenssage': (json)=>{},
		'onmessage': (json)=>{}
	}

	if(habilitado){
		if(unidade && empresa){
			// var socket = new WebSocket('wss://lamparinasws.herokuapp.com');
			socket = new WebSocket('ws://54.243.225.202:8080')
		
			// socket.onmessage = function (event){
			// 	console.log('implementação padrão')
			// };
			socket.onerror = function (event){
				console.log(event.data)
				socket.close()
			};
			socket.onclose = function (event){
				var reason;
				switch(event.code){
					case 1000: reason = "Encerramento normal, significando que o propósito para o qual a conexão foi estabelecida foi cumprido.";break;
					case 1001: reason = "Um ponto de extremidade está \"indo embora\", como um servidor que está fora do ar ou um navegador que saiu de uma página.";break;
					case 1002: reason = "Um endpoint está encerrando a conexão devido a um erro de protocolo";break;
					case 1003: reason = "Um endpoint está encerrando a conexão porque recebeu um tipo de dados que não pode aceitar (por exemplo, um endpoint que entende apenas dados de texto PODE enviar isso se receber uma mensagem binária).";break;
					case 1004: reason = "Reservado. O significado específico pode ser definido no futuro.";break;
					case 1005: reason = "Nenhum código de status estava realmente presente.";break;
					case 1006: reason = "A conexão foi fechada de forma anormal, por exemplo, sem enviar ou receber um quadro de controle Close";break;
					case 1007: reason = "Um endpoint está encerrando a conexão porque recebeu dados dentro de uma mensagem que não eram consistentes com o tipo da mensagem (por exemplo, dados não UTF-8 [https://www.rfc-editor.org/rfc/rfc3629] dentro de uma mensagem de texto).";break;
					case 1008: reason = "Um endpoint está encerrando a conexão porque recebeu uma mensagem que \"viola sua política\". Esse motivo é fornecido se não houver outro motivo susceptível ou se houver necessidade de ocultar detalhes específicos sobre a apólice.";break;
					case 1009: reason = "Um terminal está encerrando a conexão porque recebeu uma mensagem muito grande para ser processada.";break;
					case 1010: reason = "Um endpoint (cliente) está encerrando a conexão porque esperava que o servidor negociasse uma ou mais extensões, mas o servidor não as retornou na mensagem de resposta do handshake do WebSocket. <br /> Especificamente, as extensões necessárias são: " + event.reason;break;
					case 1011: reason = "Um servidor está encerrando a conexão porque encontrou uma condição inesperada que o impediu de atender à solicitação.";break;
					case 1015: reason = "A conexão foi encerrada devido a uma falha na execução de um handshake TLS (por exemplo, o certificado do servidor não pode ser verificado).";break;
					default: reason = "Rasão desconhecida";break;
				}
				console.log('WebSocket Close: '+reason)
				setTimeout(function() {WebSocketConnect(unidade,empresa)},500)
			};
			socket.onopen = function(event){
				socket.sendMenssage({'type':'SETPARAMETERS','empresa':empresa,
				'unidade':unidade});
				console.log('connected')
			}
			socket.sendMenssage = function(json){
				if(json.type){
					json.cor = json.cor?json.cor:'#FFF'
					json.timeout = json.timeout?json.timeout:1000
					json.menssage = json.menssage?json.menssage:''
					// json.empresa = json.empresa?json.empresa:''
					// json.unidade = json.unidade?json.unidade:''
	
					socket.send(JSON.stringify(json))
				}
			}
		}else{
			let msg = 'informe o id da unidade e empresa como parametro na função "WebSocketConnect"'
			console.log(msg)
			socket = false
		}
	}
	return socket
}

function cantaCaracteres(htmlTextId, numMaxCaracteres, htmlIdMostraRestantes) {
	var caracteresDigitados = $(`#${htmlTextId}`).val().length;
	var caracteresRestantes = numMaxCaracteres - caracteresDigitados;
	let inform = '';

	if (caracteresRestantes <= 0) {
		var texto = $(`#${htmlTextId}`).val();
		$(`#${htmlTextId}`).val(texto.substr(0, numMaxCaracteres));
		$(`#${htmlIdMostraRestantes}`).text("0 restantes");
	} else {
		inform = caracteresRestantes==numMaxCaracteres?'':`${caracteresRestantes} restantes`
		$(`#${htmlIdMostraRestantes}`).text(inform);
	}
}