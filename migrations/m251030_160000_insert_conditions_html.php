<?php

use yii\db\Migration;

class m251030_160000_insert_conditions_html extends Migration
{
    public function safeUp()
    {
        $html = '<div style="font-family: Arial, sans-serif; font-size: 11px; line-height: 1.6; padding: 20px;">
<h2 style="text-align: center; font-size: 16px; font-weight: bold; margin-bottom: 15px;">Reservación firme contra depósito</h2>
<h3 style="font-size: 14px; font-weight: bold; margin-top: 20px; margin-bottom: 10px;">Indicaciones Importantes:</h3>

<h4 style="font-size: 12px; font-weight: bold; margin-top: 15px; margin-bottom: 8px;">SOBRE EL RETIRO</h4>
<ol style="margin-left: 20px; padding-left: 10px;">
    <li>Revise el estado del vehículo.</li>
    <li>Revise el estado de la gasolina.</li>
    <li>Recuerde firmar la hoja de la Orden de alquiler</li>
    <li>Solicite las llaves e indicaciones sobre alarma y otros.</li>
</ol>

<h4 style="font-size: 12px; font-weight: bold; margin-top: 15px; margin-bottom: 8px;">SOBRE LA ENTREGA</h4>
<ol style="margin-left: 20px; padding-left: 10px;">
    <li>Recuerde entregar el vehículo con el tanque de gasolina lleno. En caso de no poder realizarlo indíquelo a la oficina se cobrará la gasolina + ¢15,000 iva.</li>
    <li>Recuerde revisar el estado del vehículo antes de entregarlo.</li>
    <li>En caso de emergencia o accidente debe llamar al 88781108 con Ing. Ronald.</li>
    <li>En caso de rayones o siniestros debe cancelar el monto de $800 dólares en casos mayores como accidente u otros deberá cancelar $1,000.</li>
    <li>Recuerde que el chofer siempre deberá tener licencia al día ya que es requisito para el alquiler y en temas de seguro del mismo.</li>
    <li>El corre a partir aplica únicamente retirando el auto en nuestras instalaciones.</li>
    <li>En caso de que se le realice un parte, este mismo debe ser cubierto por el responsable de la reservación.</li>
    <li>Es indispensable que el conductor se encuentre presente en el lugar de los hechos en caso de un incidente vial. La cobertura del seguro no aplicará si el conductor no está presente cuando las autoridades de tránsito lleguen al sitio, ya que su ausencia anularía la validez de la póliza. Esta cláusula es fundamental para garantizar la correcta aplicación del seguro y la protección de ambas partes involucradas. De no cumplirse el cliente es responsable al 100% por invalidar la cláusula de cobertura del seguro del vehículo.</li>
</ol>

<h4 style="font-size: 12px; font-weight: bold; margin-top: 15px; margin-bottom: 8px;">SEGURIDAD:</h4>
<ol style="margin-left: 20px; padding-left: 10px;">
    <li>Recuerde revisar el aire de las llantas y cinturones.</li>
    <li>En ningún momento dejar las llaves dentro del auto, pues la mayoría de nuestros automóviles cuentan con cierre automático.</li>
</ol>
</div>';

        // Verificar si ya existe el registro
        $exists = (new \yii\db\Query())
            ->from('company_config')
            ->where(['config_key' => 'rental_conditions_html'])
            ->exists();

        if (!$exists) {
            $this->insert('company_config', [
                'config_key' => 'rental_conditions_html',
                'config_value' => $html,
                'description' => 'Condiciones de alquiler (HTML) - Página 2 del PDF',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } else {
            // Actualizar si ya existe
            $this->update('company_config', [
                'config_value' => $html,
                'description' => 'Condiciones de alquiler (HTML) - Página 2 del PDF',
                'updated_at' => date('Y-m-d H:i:s'),
            ], ['config_key' => 'rental_conditions_html']);
        }
    }

    public function safeDown()
    {
        // No eliminar el registro, solo dejar vacío si se hace rollback
        $this->update('company_config', [
            'config_value' => '',
            'updated_at' => date('Y-m-d H:i:s'),
        ], ['config_key' => 'rental_conditions_html']);
    }
}

