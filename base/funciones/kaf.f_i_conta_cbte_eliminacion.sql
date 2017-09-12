CREATE OR REPLACE FUNCTION kaf.f_i_conta_cbte_eliminacion (
  p_id_usuario integer,
  p_id_usuario_ai integer,
  p_usuario_ai varchar,
  p_id_int_comprobante integer,
  p_conexion varchar = NULL::character varying
)
RETURNS boolean AS
$body$
/**************************************************************************
 SISTEMA:     Sistema de Activos Fijos
 FUNCION:     kaf.f_i_conta_cbte_eliminacion
 DESCRIPCION: Gestiona las acciones a seguir al eliminar el comprobante generado desde el sistema de contabilidad
 AUTOR:       RCM
 FECHA:       22/08/2017
 COMENTARIOS: 
***************************************************************************
 HISTORIAL DE MODIFICACIONES:

 DESCRIPCION: 
 AUTOR:     
 FECHA:   
***************************************************************************/
DECLARE
  
	v_nombre_funcion        text;
    v_resp                  varchar;
    v_registros             record;
    v_id_estado_actual      integer;
    va_id_tipo_estado       integer[];
    va_codigo_estado        varchar[];
    va_disparador           varchar[];
    va_regla                varchar[];
    va_prioridad            integer[];
    v_id_tipo_estado        integer;
    v_id_funcionario        integer;
    v_id_usuario_reg        integer;
    v_id_depto              integer;
    v_codigo_estado         varchar;
    v_id_estado_wf_ant      integer;
    v_id_proceso_wf         integer;
    
BEGIN

    v_nombre_funcion = 'kaf.f_i_conta_cbte_eliminacion';
    
    --Obtención de datos del movimiento y comprobante generado
    select 
    mov.id_movimiento,
    mov.id_estado_wf,
    mov.id_proceso_wf,
    mov.estado,
    mov.num_tramite,
    c.id_int_comprobante,         
    c.estado_reg,
    mov.id_int_comprobante
    into
    v_registros
    from  kaf.tmovimiento  mov
    inner join conta.tint_comprobante c
    on c.id_int_comprobante = mov.id_int_comprobante 
    where mov.id_int_comprobante = p_id_int_comprobante; 
    
   
    --Validación de existencia del movimiento
    if  v_registros.id_movimiento is NULL  THEN     
        raise exception 'El comprobante no está relacionado a ningún proceso de activos fijos';
    end if;

    --Se verifica el estado del comprobante
    if v_registros.estado_reg = 'validado' THEN
        raise exception 'No puede eliminarse el comprobante porque ya fue validado';
    end if;

    --Recupera estado anterior segun Log del WF
    SELECT  
    ps_id_tipo_estado,
    ps_id_funcionario,
    ps_id_usuario_reg,
    ps_id_depto,
    ps_codigo_estado,
    ps_id_estado_wf_ant
    into
    v_id_tipo_estado,
    v_id_funcionario,
    v_id_usuario_reg,
    v_id_depto,
    v_codigo_estado,
    v_id_estado_wf_ant 
    FROM wf.f_obtener_estado_ant_log_wf(v_registros.id_estado_wf);

    --Se obtiene el proceso del WF
    select 
    ew.id_proceso_wf 
    into 
    v_id_proceso_wf
    from wf.testado_wf ew
    where ew.id_estado_wf= v_id_estado_wf_ant;

    --Se obtiene el id del WF
    v_id_estado_actual = wf.f_registra_estado_wf(
        v_id_tipo_estado, 
        v_id_funcionario, 
        v_registros.id_estado_wf, 
        v_id_proceso_wf, 
        p_id_usuario,
        p_id_usuario_ai,
        p_usuario_ai,
        v_id_depto,
        'Eliminación del comprobante de Alta de activos fijos:'|| COALESCE(v_registros.id_int_comprobante::varchar,'NaN')
    );

    -- actualiza estado en la solicitud
    update kaf.tmovimiento mov set 
    id_estado_wf        = v_id_estado_actual,
    estado              = v_codigo_estado,
    id_usuario_mod      = p_id_usuario,
    fecha_mod           = now(),
    id_int_comprobante  = NULL,
    id_usuario_ai       = p_id_usuario_ai,
    usuario_ai          = p_usuario_ai
    where mov.id_movimiento = v_registros.id_movimiento;
  
    return true;

EXCEPTION
					
	WHEN OTHERS THEN
			v_resp='';
			v_resp = pxp.f_agrega_clave(v_resp,'mensaje',SQLERRM);
			v_resp = pxp.f_agrega_clave(v_resp,'codigo_error',SQLSTATE);
			v_resp = pxp.f_agrega_clave(v_resp,'procedimientos',v_nombre_funcion);
			raise exception '%',v_resp;
END;
$body$
LANGUAGE 'plpgsql'
VOLATILE
CALLED ON NULL INPUT
SECURITY INVOKER
COST 100;