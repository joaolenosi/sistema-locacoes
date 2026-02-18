
    // Máscaras para campos
    $(document).ready(function() {
        // Desabilitar botão de envio inicialmente
        //  desabilitarBotaoEnvio();

        // Máscara CNPJ ou CPF (detecta automaticamente)
        $('#cnpj').on('input', function() {
            let value = this.value.replace(/\D/g, '');

            // Detectar se é CPF ou CNPJ pelo tamanho
            if (value.length <= 11) {
                // Máscara CPF: 000.000.000-00
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                $('#cnpjHelp').text('CPF detectado');
            } else {
                // Máscara CNPJ: 00.000.000/0000-00
                value = value.replace(/^(\d{2})(\d)/, '$1.$2');
                value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
                value = value.replace(/(\d{4})(\d)/, '$1-$2');
                $('#cnpjHelp').text('CNPJ detectado - preenche dados automaticamente');
            }

            this.value = value;
        });

        // Máscara CPF
        $('#cpf').on('input', function() {
            let value = this.value.replace(/\D/g, '');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            this.value = value;
        });

        // Máscara CEP + busca automática no blur e depois de digitar 8 dígitos
        $('#cep, #cepOng').on('input', function() {
            let value = this.value.replace(/\D/g, '');
            // Limitar a 8 dígitos
            if (value.length > 8) value = value.substring(0, 8);
            value = value.replace(/^(\d{5})(\d)/, '$1-$2');
            this.value = value;

            // Busca automática quando atingir 8 dígitos (com debounce leve)
            const campoId = this.id;
            clearTimeout(this._cepTimeout);
            if (value.replace(/\D/g, '').length === 8) {
                this._cepTimeout = setTimeout(function() {
                    if (campoId === 'cep') {
                        buscarCEP();
                    } else if (campoId === 'cepOng') {
                        buscarCEPOng();
                    }
                }, 250);
            }
        });

        // Busca automática ao sair do campo (blur)
        $('#cep').on('blur', function() {
            const digits = this.value.replace(/\D/g, '');
            if (digits.length === 8) buscarCEP();
        });
        $('#cepOng').on('blur', function() {
            const digits = this.value.replace(/\D/g, '');
            if (digits.length === 8) buscarCEPOng();
        });

        // Máscara Telefone com limitação de caracteres
        $('#telefone, #whatsapp').on('input', function() {
            let value = this.value.replace(/\D/g, '');

            // Limitar a 11 dígitos (máximo para celular brasileiro)
            if (value.length > 11) {
                value = value.substring(0, 11);
            }

            // Aplicar máscara baseada no tamanho
            if (value.length <= 10) {
                // Telefone fixo: (11) 1234-5678
                value = value.replace(/(\d{2})(\d)/, '($1) $2');
                value = value.replace(/(\d{4})(\d)/, '$1-$2');
            } else if (value.length === 11) {
                // Celular: (11) 91234-5678
                value = value.replace(/(\d{2})(\d)/, '($1) $2');
                value = value.replace(/(\d{5})(\d)/, '$1-$2');
            }

            this.value = value;
        });

        // Validação de Senha em Tempo Real
        $('#credencial1, #credencial2').on('input', function() {
            validarSenha();
        });

        function validarSenha() {
            const senha = $('#credencial1').val();
            const confirmarSenha = $('#credencial2').val();
            const strengthFill = $('#passwordStrengthFill');
            const strengthText = $('#passwordStrengthText');
            const reqLength = $('#reqLength');
            const reqMatch = $('#reqMatch');

            // Validar comprimento mínimo (6 caracteres)
            const temComprimentoMinimo = senha.length >= 6;

            // Atualizar ícone e cor do requisito de comprimento
            if (temComprimentoMinimo) {
                reqLength.removeClass('invalid').addClass('valid');
                reqLength.find('i').removeClass('fa-times').addClass('fa-check');
            } else {
                reqLength.removeClass('valid').addClass('invalid');
                reqLength.find('i').removeClass('fa-check').addClass('fa-times');
            }

            // Validar se senhas coincidem (apenas se ambas têm conteúdo)
            let senhasCoincidem = false;
            if (senha.length > 0 && confirmarSenha.length > 0) {
                senhasCoincidem = senha === confirmarSenha;

                if (senhasCoincidem) {
                    reqMatch.removeClass('invalid').addClass('valid');
                    reqMatch.find('i').removeClass('fa-times').addClass('fa-check');
                } else {
                    reqMatch.removeClass('valid').addClass('invalid');
                    reqMatch.find('i').removeClass('fa-check').addClass('fa-times');
                }
            } else {
                reqMatch.removeClass('valid').addClass('invalid');
                reqMatch.find('i').removeClass('fa-check').addClass('fa-times');
            }

            // Calcular força da senha
            let strength = 0;
            let strengthClass = '';
            let strengthLabel = '';

            if (senha.length === 0) {
                strengthClass = '';
                strengthLabel = 'Digite uma senha';
            } else if (senha.length < 6) {
                strengthClass = 'very-weak';
                strengthLabel = 'Muito fraca';
            } else {
                // Contar critérios de força
                if (senha.length >= 6) strength += 1;
                if (senha.length >= 8) strength += 1;
                if (/[a-z]/.test(senha)) strength += 1;
                if (/[A-Z]/.test(senha)) strength += 1;
                if (/[0-9]/.test(senha)) strength += 1;
                if (/[^A-Za-z0-9]/.test(senha)) strength += 1;

                // Determinar classe e label baseado na força
                if (strength <= 1) {
                    strengthClass = 'very-weak';
                    strengthLabel = 'Muito fraca';
                } else if (strength <= 2) {
                    strengthClass = 'weak';
                    strengthLabel = 'Fraca';
                } else if (strength <= 3) {
                    strengthClass = 'medium';
                    strengthLabel = 'Média';
                } else if (strength <= 4) {
                    strengthClass = 'strong';
                    strengthLabel = 'Forte';
                } else {
                    strengthClass = 'very-strong';
                    strengthLabel = 'Muito forte';
                }
            }

            // Atualizar barra de progresso e texto
            strengthFill.removeClass('very-weak weak medium strong very-strong').addClass(strengthClass);
            strengthText.removeClass('very-weak weak medium strong very-strong').addClass(strengthClass).text(
                strengthLabel);

            // Validar campos
            const credencial1Input = $('#credencial1');
            const credencial2Input = $('#credencial2');

            if (temComprimentoMinimo) {
                credencial1Input.removeClass('is-invalid').addClass('is-valid');
                limparFeedback(credencial1Input);
            } else {
                credencial1Input.removeClass('is-valid');
            }

            if (senhasCoincidem && confirmarSenha.length > 0) {
                credencial2Input.removeClass('is-invalid').addClass('is-valid');
                limparFeedback(credencial2Input);
            } else if (confirmarSenha.length > 0) {
                credencial2Input.removeClass('is-valid').addClass('is-invalid');
                mostrarErro(credencial2Input, 'As senhas não coincidem');
            } else {
                credencial2Input.removeClass('is-valid is-invalid');
            }
        }
    });

    // Função para validar CNPJ/CPF via API e verificar se já existe no banco
    function validarCNPJ() {
        const cnpjInput = $('#cnpj');
        const documento = cnpjInput.val().replace(/\D/g, '');
        const spinner = $('#cnpjSpinner');

        // Validar tamanho (11 para CPF, 14 para CNPJ)
        if (documento.length !== 11 && documento.length !== 14) {
            mostrarErro(cnpjInput, 'Digite um CPF (11 dígitos) ou CNPJ (14 dígitos) válido');
            return;
        }

        const isCPF = documento.length === 11;
        const tipoDOcumento = isCPF ? 'CPF' : 'CNPJ';

        spinner.show();
        cnpjInput.removeClass('is-invalid is-valid');

        // Primeiro, verificar se documento já existe no banco
        $.ajax({
            url: 'verificar_cnpj.php',
            method: 'POST',
            data: {
                cnpj: documento
            },
            dataType: 'json',
            success: function(response) {
                if (response.existe) {
                    spinner.hide();
                    mostrarErro(cnpjInput, `Este ${tipoDOcumento} já está cadastrado no sistema`);

                    // Mostrar alerta SweetAlert
                    Swal.fire({
                        title: `${tipoDOcumento} já cadastrado!`,
                        html: `
                            <div class="text-center">
                                <i class="fas fa-exclamation-triangle text-warning" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                                <p>Este ${tipoDOcumento} já está registrado no sistema.</p>
                                <p><strong>O que você pode fazer:</strong></p>
                            </div>
                        `,
                        showDenyButton: true,
                        showCancelButton: true,
                        confirmButtonText: '<i class="fas fa-sign-in-alt me-2"></i>Acessar Área do Participante',
                        denyButtonText: '<i class="fas fa-user me-2"></i>Fazer Login',
                        cancelButtonText: '<i class="fas fa-times me-2"></i>Cancelar',
                        confirmButtonColor: '#1E90FF',
                        denyButtonColor: '#6c757d',
                        cancelButtonColor: '#dc3545',
                        icon: 'warning',
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'area-participante.php';
                        } else if (result.isDenied) {
                            window.location.href = 'login.php';
                        }
                    });

                    return;
                }

                // Se for CNPJ e não existe no banco, validar na API
                if (!isCPF) {
                    validarCNPJNaAPI(documento);
                } else {
                    // Se for CPF, apenas validar e marcar como válido
                    if (validarCPF(documento)) {
                        spinner.hide();
                        mostrarSucesso(cnpjInput, 'CPF válido');
                        habilitarBotaoEnvio();
                    } else {
                        spinner.hide();
                        mostrarErro(cnpjInput, 'CPF inválido');
                    }
                }
            },
            error: function() {
                // Se erro na verificação do banco
                if (!isCPF) {
                    validarCNPJNaAPI(documento);
                } else {
                    if (validarCPF(documento)) {
                        spinner.hide();
                        mostrarSucesso(cnpjInput, 'CPF válido');
                        habilitarBotaoEnvio();
                    } else {
                        spinner.hide();
                        mostrarErro(cnpjInput, 'CPF inválido');
                    }
                }
            }
        });
    }

    // Função para validar CNPJ na API Brasil
    function validarCNPJNaAPI(cnpj) {
        const cnpjInput = $('#cnpj');
        const spinner = $('#cnpjSpinner');

        // Usando a API Brasil API para consulta de CNPJ
        $.ajax({
            url: `https://brasilapi.com.br/api/cnpj/v1/${cnpj}`,
            method: 'GET',
            timeout: 10000,
            success: function(data) {
                spinner.hide();
                if (data && data.razao_social) {
                    mostrarSucesso(cnpjInput, `Empresa encontrada: ${data.razao_social}`);
                    $('#nomeOng').val(data.razao_social);

                    // Preencher endereço se disponível
                    if (data.cep) {
                        $('#cep').val(data.cep);
                        buscarCEPAutomatico(data.cep);
                    }

                    // Habilitar botão de envio
                    habilitarBotaoEnvio();

                    // Focar no campo Nome de Votação
                    setTimeout(function() {
                        $('#nomeVotacao').focus();
                    }, 500);
                } else {
                    mostrarErro(cnpjInput, 'CNPJ não encontrado na Receita Federal');
                    // Manter botão desabilitado se CNPJ inválido
                    // desabilitarBotaoEnvio();
                }
            },
            error: function(xhr) {
                spinner.hide();
                if (xhr.status === 404) {
                    mostrarErro(cnpjInput, 'CNPJ não encontrado na Receita Federal');
                } else {
                    mostrarErro(cnpjInput, 'Erro ao validar CNPJ. Tente novamente.');
                }
                // Desabilitar botão de envio em caso de erro
                //  desabilitarBotaoEnvio();
            }
        });
    }

    // Função para buscar CEP via ViaCEP
    function buscarCEP() {
        const cepInput = $('#cep');
        const cep = cepInput.val().replace(/\D/g, '');

        if (cep.length !== 8) {
            mostrarErro(cepInput, 'CEP deve ter 8 dígitos');
            return;
        }

        buscarCEPAutomatico(cep);
    }

    function buscarCEPAutomatico(cep) {
        const cepInput = $('#cep');
        const spinner = $('#cepSpinner');

        spinner.show();
        cepInput.removeClass('is-invalid is-valid');

        $.ajax({
            url: `https://viacep.com.br/ws/${cep}/json/`,
            method: 'GET',
            timeout: 10000,
            success: function(data) {
                spinner.hide();
                if (data && !data.erro) {
                    mostrarSucesso(cepInput, 'CEP encontrado');
                    $('#logradouro').val(data.logradouro);
                    $('#bairro').val(data.bairro).prop('disabled', false);
                    $('#cidade').val(data.localidade).prop('disabled', false);
                    $('#uf').val(data.uf);
                    $('#numero').focus();
                } else {
                    mostrarErro(cepInput, 'CEP não encontrado');
                    // Habilitar campos para preenchimento manual
                    $('#bairro, #cidade').prop('disabled', false).val('').attr('placeholder',
                        'Digite manualmente');
                }
            },
            error: function() {
                spinner.hide();
                mostrarErro(cepInput, 'Erro ao buscar CEP. Tente novamente.');
                // Habilitar campos para preenchimento manual
                $('#bairro, #cidade').prop('disabled', false).val('').attr('placeholder',
                    'Digite manualmente');
            }
        });
    }

    // Função para validar CPF
    function validarCPF(cpf) {
        if (typeof cpf !== 'string') cpf = String(cpf);
        cpf = cpf.replace(/\D/g, '');

        if (cpf.length !== 11) return false;

        // Verifica se todos os dígitos são iguais
        if (/^(\d)\1{10}$/.test(cpf)) return false;

        // Validação do primeiro dígito verificador
        let soma = 0;
        for (let i = 0; i < 9; i++) {
            soma += parseInt(cpf.charAt(i)) * (10 - i);
        }
        let resto = 11 - (soma % 11);
        if (resto === 10 || resto === 11) resto = 0;
        if (resto !== parseInt(cpf.charAt(9))) return false;

        // Validação do segundo dígito verificador
        soma = 0;
        for (let i = 0; i < 10; i++) {
            soma += parseInt(cpf.charAt(i)) * (11 - i);
        }
        resto = 11 - (soma % 11);
        if (resto === 10 || resto === 11) resto = 0;
        if (resto !== parseInt(cpf.charAt(10))) return false;

        return true;
    }

    // Função para validar email
    function validarEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }
