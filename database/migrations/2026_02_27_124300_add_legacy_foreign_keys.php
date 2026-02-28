<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    /**
     * Migracao/refatoracao do legado: aplica foreign keys apos criacao das tabelas.
     * FKs antes ligadas a colunas legadas de usuario agora apontam para users.id.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(<<<'SQL'
ALTER TABLE `t_fornecedor`
  ADD CONSTRAINT `t_fornecedor_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_marcapneu`
  ADD CONSTRAINT `t_marcapneu_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_modelopneu`
  ADD CONSTRAINT `t_modelopneu_ibfk_1` FOREIGN KEY (`MARP_CODIGO`) REFERENCES `t_marcapneu` (`MARP_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_modelopneu_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_medidapneu`
  ADD CONSTRAINT `t_medidapneu_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_tipo`
  ADD CONSTRAINT `t_tipo_ibfk_1` FOREIGN KEY (`MARP_CODIGO`) REFERENCES `t_marcapneu` (`MARP_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_tipo_ibfk_2` FOREIGN KEY (`MODP_CODIGO`) REFERENCES `t_modelopneu` (`MODP_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_tipo_ibfk_3` FOREIGN KEY (`MEDP_CODIGO`) REFERENCES `t_medidapneu` (`MEDP_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_tipo_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_marcaveiculo`
  ADD CONSTRAINT `t_marcaveiculo_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_modeloveiculo`
  ADD CONSTRAINT `t_modeloveiculo_ibfk_1` FOREIGN KEY (`MARV_CODIGO`) REFERENCES `t_marcaveiculo` (`MARV_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_modeloveiculo_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_veiculo`
  ADD CONSTRAINT `t_veiculo_ibfk_1` FOREIGN KEY (`MODV_CODIGO`) REFERENCES `t_modeloveiculo` (`MODV_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_veiculo_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_veiculo_ibfk_3` FOREIGN KEY (`UNI_CODIGO`) REFERENCES `t_clienteunidade` (`UNI_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_veiculo_ibfk_4` FOREIGN KEY (`VEIC_CODIGO`) REFERENCES `t_veiculoconfiguracao` (`VEIC_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_pneu`
  ADD CONSTRAINT `t_pneu_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_pneu_ibfk_3` FOREIGN KEY (`ITS_CODIGO`) REFERENCES `t_itensnfcomprapneus` (`ITS_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_pneu_ibfk_4` FOREIGN KEY (`UNI_CODIGO`) REFERENCES `t_clienteunidade` (`UNI_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_pneu_ibfk_5` FOREIGN KEY (`TIPO_CODIGO`) REFERENCES `t_tipo` (`TIPO_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_movimentacao`
  ADD CONSTRAINT `t_movimentacao_ibfk_1` FOREIGN KEY (`PNE_CODIGO`) REFERENCES `t_pneu` (`PNE_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_movimentacao_ibfk_2` FOREIGN KEY (`POS_CODIGO`) REFERENCES `t_posicao` (`POS_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_movimentacao_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_movimentacao_ibfk_4` FOREIGN KEY (`VEI_CODIGO`) REFERENCES `t_veiculo` (`VEI_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_pneuatual`
  ADD CONSTRAINT `t_pneuatual_ibfk_1` FOREIGN KEY (`MOV_CODIGO`) REFERENCES `t_movimentacao` (`MOV_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_pneuatual_ibfk_2` FOREIGN KEY (`PNE_CODIGO`) REFERENCES `t_pneu` (`PNE_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_pneuatual_ibfk_3` FOREIGN KEY (`POS_CODIGO`) REFERENCES `t_posicao` (`POS_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_pneuatual_ibfk_4` FOREIGN KEY (`VEI_CODIGO`) REFERENCES `t_veiculo` (`VEI_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_inspecao`
  ADD CONSTRAINT `t_inspecao_ibfk_1` FOREIGN KEY (`VEI_CODIGO`) REFERENCES `t_veiculo` (`VEI_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_inspecao_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_mmleitura`
  ADD CONSTRAINT `t_mmleitura_ibfk_1` FOREIGN KEY (`MOV_CODIGO`) REFERENCES `t_movimentacao` (`MOV_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_mmleitura_ibfk_2` FOREIGN KEY (`VEI_CODIGO`) REFERENCES `t_veiculo` (`VEI_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_mmleitura_ibfk_3` FOREIGN KEY (`PNE_CODIGO`) REFERENCES `t_pneu` (`PNE_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_mmleitura_ibfk_4` FOREIGN KEY (`INSP_CODIGO`) REFERENCES `t_inspecao` (`INSP_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_calibragem`
  ADD CONSTRAINT `t_calibragem_ibfk_1` FOREIGN KEY (`MOV_CODIGO`) REFERENCES `t_movimentacao` (`MOV_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_calibragem_ibfk_2` FOREIGN KEY (`VEI_CODIGO`) REFERENCES `t_veiculo` (`VEI_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_calibragem_ibfk_3` FOREIGN KEY (`PNE_CODIGO`) REFERENCES `t_pneu` (`PNE_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_calibragem_ibfk_4` FOREIGN KEY (`INSP_CODIGO`) REFERENCES `t_inspecao` (`INSP_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_calibragem_ibfk_5` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_saida`
  ADD CONSTRAINT `t_saida_ibfk_1` FOREIGN KEY (`FORN_CODIGO`) REFERENCES `t_fornecedor` (`FORN_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_saida_ibfk_2` FOREIGN KEY (`UNI_CODIGO`) REFERENCES `t_clienteunidade` (`UNI_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_saida_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_itenssaida`
  ADD CONSTRAINT `t_itenssaida_ibfk_1` FOREIGN KEY (`SAIDA_CODIGO`) REFERENCES `t_saida` (`SAIDA_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_itenssaida_ibfk_2` FOREIGN KEY (`PNE_CODIGO`) REFERENCES `t_pneu` (`PNE_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_itenssaida_ibfk_3` FOREIGN KEY (`FORN_CODIGO`) REFERENCES `t_fornecedor` (`FORN_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_retornopneu`
  ADD CONSTRAINT `t_retornopneu_ibfk_1` FOREIGN KEY (`FORN_CODIGO`) REFERENCES `t_fornecedor` (`FORN_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_retornopneu_ibfk_2` FOREIGN KEY (`UNI_CODIGO`) REFERENCES `t_clienteunidade` (`UNI_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_retornopneu_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_itensretorno`
  ADD CONSTRAINT `t_itensretorno_ibfk_1` FOREIGN KEY (`RETPNE_CODIGO`) REFERENCES `t_retornopneu` (`RETPNE_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_itensretorno_ibfk_2` FOREIGN KEY (`PNE_CODIGO`) REFERENCES `t_pneu` (`PNE_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_itensretorno_ibfk_3` FOREIGN KEY (`TIPO_CODIGO`) REFERENCES `t_tipo` (`TIPO_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_nfcomprapneus`
  ADD CONSTRAINT `t_nfcomprapneus_ibfk_2` FOREIGN KEY (`FORN_CODIGO`) REFERENCES `t_fornecedor` (`FORN_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_nfcomprapneus_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_itensnfcomprapneus`
  ADD CONSTRAINT `t_itensnfcomprapneus_ibfk_1` FOREIGN KEY (`NF_CODIGO`) REFERENCES `t_nfcomprapneus` (`NF_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_itensnfcomprapneus_ibfk_2` FOREIGN KEY (`UNI_CODIGO`) REFERENCES `t_clienteunidade` (`UNI_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_itensnfcomprapneus_ibfk_3` FOREIGN KEY (`TIPO_CODIGO`) REFERENCES `t_tipo` (`TIPO_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_transferenciapneus`
  ADD CONSTRAINT `FK_UNI_DESTINO` FOREIGN KEY (`unidadeDestino`) REFERENCES `t_clienteunidade` (`UNI_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `FK_UNI_REMETENTE` FOREIGN KEY (`unidadeRemetente`) REFERENCES `t_clienteunidade` (`UNI_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `FK_USUARIO_CADASTRO` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_itens_transferenciapneus`
  ADD CONSTRAINT `FK_PNECODIGO_TRANSF` FOREIGN KEY (`pneuCodigo`) REFERENCES `t_pneu` (`PNE_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `FK_TransferenciaPneu` FOREIGN KEY (`idTransferencia`) REFERENCES `t_transferenciapneus` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `FK_USUARIO_RETORNO_TRANS` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_recebimento_transferenciapneus`
  ADD CONSTRAINT `t_recebimento_transferenciapneus_ibfk_1` FOREIGN KEY (`unidadeRecebimento`) REFERENCES `t_clienteunidade` (`UNI_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_recebimento_transferenciapneus_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_auditoria_detalhes`
  ADD CONSTRAINT `fk_audit_sessao` FOREIGN KEY (`audit_id`) REFERENCES `t_auditoria_sessao` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_auditoria_estoque_status`
  ADD CONSTRAINT `fk_audit_ref` FOREIGN KEY (`audit_id`) REFERENCES `t_auditoria_sessao` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_auditoria_relatorio`
  ADD CONSTRAINT `fk_rel_audit` FOREIGN KEY (`audit_id`) REFERENCES `t_auditoria_sessao` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_log_usuario`
  ADD CONSTRAINT `t_log_usuario_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_baixapneu`
  ADD CONSTRAINT `t_baixapneu_ibfk_1` FOREIGN KEY (`PNE_CODIGO`) REFERENCES `t_pneu` (`PNE_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_baixapneu_ibfk_2` FOREIGN KEY (`MOPA_CODIGO`) REFERENCES `t_motivoxpai` (`MOPA_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_motivopneu`
  ADD CONSTRAINT `t_motivopneu_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_motivoxpai`
  ADD CONSTRAINT `t_motivoxpai_ibfk_1` FOREIGN KEY (`MOTPAI_CODIGO`) REFERENCES `t_motivopai` (`MOTPAI_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_motivoxpai_ibfk_2` FOREIGN KEY (`MOTP_CODIGO`) REFERENCES `t_motivopneu` (`MOTP_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_posicaoxconfiguracao`
  ADD CONSTRAINT `t_posicaoxconfiguracao_PK_ibfk_1` FOREIGN KEY (`POS_CODIGO`) REFERENCES `t_posicao` (`POS_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `t_posicaoxconfiguracao_PK_ibfk_2` FOREIGN KEY (`VEIC_CODIGO`) REFERENCES `t_veiculoconfiguracao` (`VEIC_CODIGO`) ON DELETE RESTRICT ON UPDATE RESTRICT
SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(<<<'SQL'
ALTER TABLE `t_fornecedor` DROP FOREIGN KEY `t_fornecedor_ibfk_1`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_marcapneu` DROP FOREIGN KEY `t_marcapneu_ibfk_1`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_modelopneu` DROP FOREIGN KEY `t_modelopneu_ibfk_1`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_modelopneu` DROP FOREIGN KEY `t_modelopneu_ibfk_2`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_medidapneu` DROP FOREIGN KEY `t_medidapneu_ibfk_1`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_tipo` DROP FOREIGN KEY `t_tipo_ibfk_1`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_tipo` DROP FOREIGN KEY `t_tipo_ibfk_2`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_tipo` DROP FOREIGN KEY `t_tipo_ibfk_3`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_tipo` DROP FOREIGN KEY `t_tipo_ibfk_4`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_marcaveiculo` DROP FOREIGN KEY `t_marcaveiculo_ibfk_1`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_modeloveiculo` DROP FOREIGN KEY `t_modeloveiculo_ibfk_1`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_modeloveiculo` DROP FOREIGN KEY `t_modeloveiculo_ibfk_2`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_veiculo` DROP FOREIGN KEY `t_veiculo_ibfk_1`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_veiculo` DROP FOREIGN KEY `t_veiculo_ibfk_2`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_veiculo` DROP FOREIGN KEY `t_veiculo_ibfk_3`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_veiculo` DROP FOREIGN KEY `t_veiculo_ibfk_4`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_pneu` DROP FOREIGN KEY `t_pneu_ibfk_2`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_pneu` DROP FOREIGN KEY `t_pneu_ibfk_3`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_pneu` DROP FOREIGN KEY `t_pneu_ibfk_4`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_pneu` DROP FOREIGN KEY `t_pneu_ibfk_5`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_movimentacao` DROP FOREIGN KEY `t_movimentacao_ibfk_1`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_movimentacao` DROP FOREIGN KEY `t_movimentacao_ibfk_2`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_movimentacao` DROP FOREIGN KEY `t_movimentacao_ibfk_3`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_movimentacao` DROP FOREIGN KEY `t_movimentacao_ibfk_4`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_pneuatual` DROP FOREIGN KEY `t_pneuatual_ibfk_1`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_pneuatual` DROP FOREIGN KEY `t_pneuatual_ibfk_2`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_pneuatual` DROP FOREIGN KEY `t_pneuatual_ibfk_3`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_pneuatual` DROP FOREIGN KEY `t_pneuatual_ibfk_4`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_inspecao` DROP FOREIGN KEY `t_inspecao_ibfk_1`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_inspecao` DROP FOREIGN KEY `t_inspecao_ibfk_2`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_mmleitura` DROP FOREIGN KEY `t_mmleitura_ibfk_1`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_mmleitura` DROP FOREIGN KEY `t_mmleitura_ibfk_2`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_mmleitura` DROP FOREIGN KEY `t_mmleitura_ibfk_3`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_mmleitura` DROP FOREIGN KEY `t_mmleitura_ibfk_4`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_calibragem` DROP FOREIGN KEY `t_calibragem_ibfk_1`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_calibragem` DROP FOREIGN KEY `t_calibragem_ibfk_2`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_calibragem` DROP FOREIGN KEY `t_calibragem_ibfk_3`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_calibragem` DROP FOREIGN KEY `t_calibragem_ibfk_4`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_calibragem` DROP FOREIGN KEY `t_calibragem_ibfk_5`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_saida` DROP FOREIGN KEY `t_saida_ibfk_1`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_saida` DROP FOREIGN KEY `t_saida_ibfk_2`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_saida` DROP FOREIGN KEY `t_saida_ibfk_3`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_itenssaida` DROP FOREIGN KEY `t_itenssaida_ibfk_1`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_itenssaida` DROP FOREIGN KEY `t_itenssaida_ibfk_2`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_itenssaida` DROP FOREIGN KEY `t_itenssaida_ibfk_3`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_retornopneu` DROP FOREIGN KEY `t_retornopneu_ibfk_1`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_retornopneu` DROP FOREIGN KEY `t_retornopneu_ibfk_2`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_retornopneu` DROP FOREIGN KEY `t_retornopneu_ibfk_3`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_itensretorno` DROP FOREIGN KEY `t_itensretorno_ibfk_1`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_itensretorno` DROP FOREIGN KEY `t_itensretorno_ibfk_2`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_itensretorno` DROP FOREIGN KEY `t_itensretorno_ibfk_3`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_nfcomprapneus` DROP FOREIGN KEY `t_nfcomprapneus_ibfk_2`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_nfcomprapneus` DROP FOREIGN KEY `t_nfcomprapneus_ibfk_3`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_itensnfcomprapneus` DROP FOREIGN KEY `t_itensnfcomprapneus_ibfk_1`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_itensnfcomprapneus` DROP FOREIGN KEY `t_itensnfcomprapneus_ibfk_2`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_itensnfcomprapneus` DROP FOREIGN KEY `t_itensnfcomprapneus_ibfk_3`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_transferenciapneus` DROP FOREIGN KEY `FK_UNI_DESTINO`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_transferenciapneus` DROP FOREIGN KEY `FK_UNI_REMETENTE`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_transferenciapneus` DROP FOREIGN KEY `FK_USUARIO_CADASTRO`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_itens_transferenciapneus` DROP FOREIGN KEY `FK_PNECODIGO_TRANSF`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_itens_transferenciapneus` DROP FOREIGN KEY `FK_TransferenciaPneu`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_itens_transferenciapneus` DROP FOREIGN KEY `FK_USUARIO_RETORNO_TRANS`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_recebimento_transferenciapneus` DROP FOREIGN KEY `t_recebimento_transferenciapneus_ibfk_1`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_recebimento_transferenciapneus` DROP FOREIGN KEY `t_recebimento_transferenciapneus_ibfk_2`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_auditoria_detalhes` DROP FOREIGN KEY `fk_audit_sessao`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_auditoria_estoque_status` DROP FOREIGN KEY `fk_audit_ref`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_auditoria_relatorio` DROP FOREIGN KEY `fk_rel_audit`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_log_usuario` DROP FOREIGN KEY `t_log_usuario_ibfk_1`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_baixapneu` DROP FOREIGN KEY `t_baixapneu_ibfk_1`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_baixapneu` DROP FOREIGN KEY `t_baixapneu_ibfk_2`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_motivopneu` DROP FOREIGN KEY `t_motivopneu_ibfk_1`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_motivoxpai` DROP FOREIGN KEY `t_motivoxpai_ibfk_1`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_motivoxpai` DROP FOREIGN KEY `t_motivoxpai_ibfk_2`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_posicaoxconfiguracao` DROP FOREIGN KEY `t_posicaoxconfiguracao_PK_ibfk_1`
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_posicaoxconfiguracao` DROP FOREIGN KEY `t_posicaoxconfiguracao_PK_ibfk_2`
SQL);
    }
};
