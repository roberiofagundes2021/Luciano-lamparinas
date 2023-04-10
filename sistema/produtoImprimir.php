<?php 

include_once("sessao.php"); 

include('global_assets/php/conexao.php');

use Mpdf\Mpdf;

require_once 'global_assets/php/vendor/autoload.php';

$sql = "SELECT EmpreId, EmpreCnpj, EmpreRazaoSocial, EmpreNomeFantasia, EmpreStatus, dbo.fnLicencaVencimento(EmpreId) as Licenca
		FROM Empresa
		LEFT JOIN Licenca on LicenEmpresa = EmpreId
		ORDER BY EmpreNomeFantasia ASC";
$result = $conn->query($sql);
$row = $result->fetchAll(PDO::FETCH_ASSOC);

try {
    $mpdf = new Mpdf([
        'mode' => 'utf-8', 
        'format' => [190, 236], 
        'orientation' => 'L'
	]);
	
	$html = '<table class="table datatable-responsive">
				<thead>
					<tr class="bg-slate">
						<th>Nome Fantasia</th>
						<th>Razão Social</th>
						<th>CNPJ</th>
						<th>Situação</th>
						<th>Fim Licença</th>
					</tr>
				</thead>
				<tbody>';

	foreach ($row as $item){
		
		$situacao = $item['EmpreStatus'] ? 'Ativo' : 'Inativo';
		$situacaoClasse = $item['EmpreStatus'] ? 'badge-success' : 'badge-secondary';
										
		$html .= '
		<tr>
			<td>'.$item['EmpreNomeFantasia'].'</td>
			<td>'.$item['EmpreRazaoSocial'].'</td>
			<td>'.formatarCnpj($item['EmpreCnpj']).'</td>';
		

		$html .= '<td><span class="badge '.$situacaoClasse.'">'.$situacao.'</span></td>';
		
		$html .= '<td><span>'.$item['Licenca'].'</span></td>';
		
	}

	$html .= '
				</tbody>
			</table>
	';
    
    $mpdf->WriteHTML($html);
    
    // Other code
    $mpdf->Output();
    
} catch (\Mpdf\MpdfException $e) { // Note: safer fully qualified exception name used for catch
    
    // Process the exception, log, print etc.
    echo $e->getMessage();
}
