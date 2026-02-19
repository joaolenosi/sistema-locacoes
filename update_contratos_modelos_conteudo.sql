-- =============================================================================
-- Atualiza o modelo padrão de contrato (id = 1) com formatação HTML para
-- título e cláusulas em negrito. Execute no banco para melhorar o PDF.
-- =============================================================================

UPDATE `contratos_modelos`
SET `con_conteudo` = '<p><strong>CONTRATO DE LOCAÇÃO DE VEÍCULO AUTOMOTOR</strong></p>

<p>Pelo presente instrumento particular, de um lado {{locadora.nome_completo}}, inscrita no CPF/CNPJ sob o nº {{locadora.cpf_cnpj}}, com endereço à {{locadora.endereco}}, nº {{locadora.numero}}, {{locadora.complemento}}, bairro {{locadora.bairro}}, {{locadora.cidade}} – {{locadora.estado}}, CEP {{locadora.cep}}, doravante denominada LOCADORA;</p>

<p>e, de outro lado, {{locatario.nome_completo}}, inscrito no CPF/CNPJ sob o nº {{locatario.cpf_cnpj}}, portador da CNH nº {{locatario.cnh_numero}}, com vencimento em {{locatario.cnh_vencimento}}, residente e domiciliado à {{locatario.endereco}}, nº {{locatario.numero}}, {{locatario.complemento}}, bairro {{locatario.bairro}}, {{locatario.cidade}} – {{locatario.estado}}, CEP {{locatario.cep}}, telefone {{locatario.telefone}}, WhatsApp {{locatario.whatsapp}}, doravante denominado LOCATÁRIO, têm entre si justo e contratado o que segue:</p>

<p><strong>CLÁUSULA 1ª – DO OBJETO</strong></p>
<p>O presente contrato tem como objeto a locação do veículo automotor abaixo descrito:</p>
<p>Marca: {{veiculo.marca}}<br>Modelo: {{veiculo.modelo}}<br>Ano: {{veiculo.ano}}<br>Cor: {{veiculo.cor}}<br>Placa: {{veiculo.placa}}<br>Chassi: {{veiculo.chassi}}<br>Renavam: {{veiculo.renavam}}<br>Tipo: {{veiculo.tipo}}</p>

<p><strong>CLÁUSULA 2ª – DO PRAZO</strong></p>
<p>A locação terá início em {{locacao.data_inicio}}, pelo período de {{locacao.tempo}}, conforme condições acordadas entre as partes.</p>

<p><strong>CLÁUSULA 3ª – DO VALOR E FORMA DE PAGAMENTO</strong></p>
<p>Pela locação, o LOCATÁRIO pagará à LOCADORA o valor total de {{locacao.valor}}, conforme a recorrência de pagamento definida em {{locacao.recorrencia_pagamento}}, com início em {{locacao.inicio_pagamento}}.</p>
<p>Em caso de atraso, incidirá multa no valor de {{locacao.taxa_multa}} e juros de {{locacao.taxa_juros}}, calculados conforme legislação vigente.</p>

<p><strong>CLÁUSULA 4ª – DA CAUÇÃO</strong></p>
<p>Como garantia do cumprimento das obrigações contratuais, o LOCATÁRIO prestará caução no valor de {{locacao.valor_caucao}}, a ser devolvida ao final da locação, desde que inexistam débitos, danos ou pendências.</p>

<p><strong>CLÁUSULA 5ª – DO USO E CONSERVAÇÃO DO VEÍCULO</strong></p>
<p>O LOCATÁRIO compromete-se a utilizar o veículo de forma responsável, zelando por sua conservação, responsabilizando-se por danos causados por mau uso, negligência ou imprudência.</p>
<p>O veículo será entregue com quilometragem registrada de {{veiculo.km_na_retirada}}, devendo ser devolvido em condições compatíveis com o uso regular.</p>

<p><strong>CLÁUSULA 6ª – DAS OBRIGAÇÕES DO LOCATÁRIO</strong></p>
<p>São obrigações do LOCATÁRIO:</p>
<p>a) Manter o veículo em boas condições de uso;<br>b) Arcar com multas, infrações e danos ocorridos durante o período da locação;<br>c) Não ceder, emprestar ou sublocar o veículo sem autorização da LOCADORA;<br>d) Respeitar a legislação de trânsito vigente.</p>

<p><strong>CLÁUSULA 7ª – DA RESCISÃO</strong></p>
<p>O presente contrato poderá ser rescindido por qualquer das partes em caso de descumprimento de quaisquer de suas cláusulas, sem prejuízo das penalidades cabíveis e da apuração de eventuais perdas e danos. Caso ocorra rescisão antecipada, permanecem devidos os valores proporcionais ao período utilizado, bem como encargos, multas e despesas decorrentes do uso do veículo, quando aplicáveis.</p>

<p><strong>CLÁUSULA 8ª – DO FORO</strong></p>
<p>Para dirimir quaisquer controvérsias oriundas deste contrato, as partes elegem o foro da comarca de {{locadora.cidade}} – {{locadora.estado}}, renunciando a qualquer outro, por mais privilegiado que seja.</p>

<p>E, por estarem assim justas e contratadas, firmam o presente instrumento na data de {{data_de_hoje}}.</p>',
    `updated_at` = CURRENT_TIMESTAMP
WHERE `id` = 1;
