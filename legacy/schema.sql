-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 27/02/2026 às 11:28
-- Versão do servidor: 8.0.45
-- Versão do PHP: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


-- --------------------------------------------------------

--
-- Estrutura para tabela `t_acessousuario`
--

CREATE TABLE `t_acessousuario` (
  `ACE_CODIGO` int NOT NULL,
  `ACE_PAGINA` varchar(50) NOT NULL,
  `ACE_VISUALIZA` tinyint(1) NOT NULL,
  `ACE_EDITA` tinyint(1) NOT NULL,
  `ACE_EXCLUI` tinyint(1) NOT NULL,
  `USU_CODIGOACESSO` int NOT NULL,
  `USU_CODIGOCADASTRO` int NOT NULL,
  `ACE_DATA` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_alocacaoveiculo`
--

CREATE TABLE `t_alocacaoveiculo` (
  `ALOC_CODIGO` int NOT NULL,
  `VEI_CODIGO` int NOT NULL,
  `USU_CODIGO` int NOT NULL,
  `USU_MOTORISTA` int NOT NULL,
  `ALOC_DATA` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ALOC_TIPO` varchar(2) NOT NULL,
  `ALOC_DATAINICIO` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_atualizacao`
--

CREATE TABLE `t_atualizacao` (
  `ATU_CODIGO` int NOT NULL,
  `ATU_EXECUTADO` int NOT NULL DEFAULT '0',
  `ATU_DESCRICAO` text NOT NULL,
  `ATU_SCRIPT` text NOT NULL,
  `ATU_DATA` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_auditoria_detalhes`
--

CREATE TABLE `t_auditoria_detalhes` (
  `id` int NOT NULL,
  `audit_id` int NOT NULL,
  `pne_codigo` int DEFAULT NULL,
  `pne_fogo` varchar(50) NOT NULL,
  `status_esperado` varchar(20) NOT NULL,
  `status_encontrado` varchar(20) NOT NULL,
  `uni_esperada` int DEFAULT NULL,
  `uni_encontrada` int DEFAULT NULL,
  `tipo_divergencia` enum('D1','D2','D3','D4','D5','D6','D7','D8') DEFAULT NULL,
  `foto_path` varchar(255) DEFAULT NULL,
  `data_registro` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_auditoria_estoque_status`
--

CREATE TABLE `t_auditoria_estoque_status` (
  `id` int NOT NULL,
  `audit_id` int NOT NULL,
  `status` varchar(20) NOT NULL,
  `total` int NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_auditoria_relatorio`
--

CREATE TABLE `t_auditoria_relatorio` (
  `id` int NOT NULL,
  `audit_id` int NOT NULL,
  `conf_usu_codigo` int NOT NULL,
  `rel_titulo` varchar(150) NOT NULL,
  `rel_texto` mediumtext NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_auditoria_sessao`
--

CREATE TABLE `t_auditoria_sessao` (
  `id` int NOT NULL,
  `uni_codigo` int NOT NULL,
  `usu_codigo` int NOT NULL,
  `usu_participantes` varchar(255) DEFAULT NULL,
  `data_inicio` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_fim` datetime DEFAULT NULL,
  `resultado` enum('ANDAMENTO','CONCLUIDO','DIVERGENCIAS','CANCELADO') NOT NULL DEFAULT 'ANDAMENTO',
  `acuracidade` decimal(5,2) DEFAULT NULL,
  `conf_status` enum('PENDENTE','ANDAMENTO','CONCLUIDO','CANCELADO') NOT NULL DEFAULT 'PENDENTE',
  `conf_usu_codigo` int DEFAULT NULL,
  `conf_data_inicio` datetime DEFAULT NULL,
  `conf_data_fim` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_baixapneu`
--

CREATE TABLE `t_baixapneu` (
  `BAI_CODIGO` int NOT NULL,
  `PNE_CODIGO` int NOT NULL,
  `MOPA_CODIGO` int NOT NULL,
  `BAI_DESCRICAO` varchar(500) NOT NULL,
  `BAI_DATA` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `BAI_STATUS` char(1) NOT NULL DEFAULT 'F' COMMENT 'P=Pendente, F=Finalizado',
  `BAI_USU_SOLICITA` int DEFAULT NULL,
  `BAI_USU_EFETIVA` int DEFAULT NULL,
  `BAI_DT_SOLICITA` datetime DEFAULT NULL,
  `BAI_DT_EFETIVA` datetime DEFAULT NULL,
  `BAI_IMGDANO` varchar(50) DEFAULT NULL,
  `BAI_IMGFOGO` varchar(50) DEFAULT NULL,
  `BAI_IMGMM` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_calibragem`
--

CREATE TABLE `t_calibragem` (
  `CAL_CODIGO` int NOT NULL,
  `MOV_CODIGO` int NOT NULL,
  `VEI_CODIGO` int DEFAULT NULL,
  `PNE_CODIGO` int NOT NULL,
  `CAL_ENCONTRADA` int NOT NULL,
  `CAL_AJUSTADA` int NOT NULL,
  `INSP_CODIGO` int DEFAULT NULL,
  `USU_CODIGO` int NOT NULL,
  `CAL_DATA` date NOT NULL,
  `CAL_DATALANCAMENTO` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_cliente`
--

CREATE TABLE `t_cliente` (
  `CLI_CODIGO` int NOT NULL,
  `CLI_CNPJ` varchar(16) NOT NULL,
  `CLI_RAZAO` varchar(50) NOT NULL,
  `CLI_FANTASIA` varchar(40) NOT NULL,
  `CLI_CPK_CORTE` float NOT NULL DEFAULT '20000',
  `CLI_STATUS` varchar(1) NOT NULL,
  `CLI_BLOQUEIO` varchar(1) NOT NULL,
  `CLI_LOGONOME` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_clientepreferencia`
--

CREATE TABLE `t_clientepreferencia` (
  `PREF_CODIGO` int NOT NULL,
  `PREF_QTD_MESES` int NOT NULL DEFAULT '6',
  `PREF_DIAS_INSPECAO` int NOT NULL DEFAULT '30',
  `PREF_USU_CADASTRO` int NOT NULL,
  `PREF_DATA_CADASTRO` datetime NOT NULL,
  `PREF_MIN_CPK` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_clienteunidade`
--

CREATE TABLE `t_clienteunidade` (
  `UNI_CODIGO` int NOT NULL,
  `UNI_DESCRICAO` varchar(40) NOT NULL,
  `UNI_STATUS` varchar(1) NOT NULL,
  `CLI_CNPJ` varchar(16) NOT NULL,
  `CLI_UF` char(2) NOT NULL,
  `CLI_CIDADE` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_fornecedor`
--

CREATE TABLE `t_fornecedor` (
  `FORN_CODIGO` int NOT NULL,
  `FORN_CNPJ` varchar(16) NOT NULL,
  `FORN_RAZAO` varchar(50) NOT NULL,
  `FORN_TELEFONE` varchar(15) DEFAULT NULL,
  `FORN_EMAIL` varchar(35) DEFAULT NULL,
  `FORN_STATUS` varchar(2) NOT NULL,
  `USU_CODIGO` int NOT NULL,
  `FORN_DATACADASTRO` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_inspecao`
--

CREATE TABLE `t_inspecao` (
  `INSP_CODIGO` int NOT NULL,
  `INSP_OBSERVACAO` varchar(100) DEFAULT NULL,
  `INSP_TIPO` varchar(30) DEFAULT NULL,
  `INSP_KMATUAL` int NOT NULL,
  `VEI_CODIGO` int NOT NULL,
  `UNI_CODIGO` int DEFAULT NULL,
  `INSP_STATUS` varchar(2) NOT NULL,
  `INSP_TEMPOSEGUNDOS` double DEFAULT NULL,
  `INSP_DATAINSPECAO` date NOT NULL,
  `INSP_DATACADASTRO` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `INSP_DATAFECHAMENTO` timestamp NULL DEFAULT NULL,
  `USU_CODIGO` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_itensnfcomprapneus`
--

CREATE TABLE `t_itensnfcomprapneus` (
  `ITS_CODIGO` int NOT NULL,
  `ITS_QNT` int NOT NULL,
  `ITS_FOGOINI` int NOT NULL,
  `ITS_STATUS` varchar(1) DEFAULT NULL,
  `TIPO_CODIGO` int NOT NULL,
  `NF_CODIGO` int NOT NULL,
  `ITS_VALORTOTAL` double NOT NULL,
  `TIPO_CODIGORECAPE` int DEFAULT NULL,
  `PNE_STATUS` varchar(2) NOT NULL,
  `ITS_DOT` varchar(10) DEFAULT NULL,
  `PNE_VIDA` varchar(3) NOT NULL,
  `UNI_CODIGO` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_itensretorno`
--

CREATE TABLE `t_itensretorno` (
  `ITRT_CODIGO` int NOT NULL,
  `ITEM_SAIDA` int DEFAULT NULL,
  `RETPNE_CODIGO` int NOT NULL,
  `PNE_CODIGO` int NOT NULL,
  `ITRT_TIPORETORNO` varchar(2) NOT NULL,
  `ITRT_STATUS` varchar(2) NOT NULL,
  `TIPO_CODIGO` int DEFAULT NULL,
  `ITRT_VALOR` double DEFAULT NULL,
  `ITRT_DESCRICAO` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_itenssaida`
--

CREATE TABLE `t_itenssaida` (
  `ITSD_CODIGO` int NOT NULL,
  `SAIDA_CODIGO` int NOT NULL,
  `PNE_CODIGO` int NOT NULL,
  `ITSD_PNEKM` int NOT NULL,
  `ITSD_TIPOSAIDA` varchar(2) NOT NULL,
  `FORN_CODIGO` int NOT NULL,
  `ITSD_STATUS` varchar(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_itens_recebimento`
--

CREATE TABLE `t_itens_recebimento` (
  `idItem` int NOT NULL,
  `idRecebimento` int NOT NULL,
  `pneuCodigo` int NOT NULL,
  `observacao` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_itens_transferenciapneus`
--

CREATE TABLE `t_itens_transferenciapneus` (
  `idItem` int NOT NULL,
  `idTransferencia` int NOT NULL,
  `pneuCodigo` int NOT NULL,
  `statusPneuEnvio` varchar(2) NOT NULL COMMENT 'Status de como o pneu foi enviado e como deve retornar',
  `dataRetorno` date DEFAULT NULL,
  `dataRetornoCadastro` datetime DEFAULT NULL,
  `usuarioRetorno` int DEFAULT NULL,
  `status` varchar(2) NOT NULL,
  `observacao` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_log_usuario`
--

CREATE TABLE `t_log_usuario` (
  `log_id` int NOT NULL,
  `usu_codigo` int NOT NULL,
  `session_id` varchar(128) NOT NULL,
  `login_time` datetime NOT NULL,
  `logout_time` datetime DEFAULT NULL,
  `session_duration` int DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_marcapneu`
--

CREATE TABLE `t_marcapneu` (
  `MARP_CODIGO` int NOT NULL,
  `MARP_DESCRICAO` varchar(30) NOT NULL,
  `MARP_STATUS` varchar(1) NOT NULL,
  `MARP_TIPO` varchar(2) NOT NULL,
  `USU_CODIGO` int NOT NULL,
  `MARP_DATACADASTRO` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_marcaveiculo`
--

CREATE TABLE `t_marcaveiculo` (
  `MARV_CODIGO` int NOT NULL,
  `MARV_DESCRICAO` varchar(30) NOT NULL,
  `MARV_STATUS` varchar(2) NOT NULL,
  `USU_CODIGO` int NOT NULL,
  `MARV_DATACADASTRO` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_medidapneu`
--

CREATE TABLE `t_medidapneu` (
  `MEDP_DESCRICAO` varchar(30) NOT NULL,
  `MEDP_CODIGO` int NOT NULL,
  `CAL_RECOMENDADA` double DEFAULT NULL,
  `MEDP_STATUS` varchar(1) NOT NULL,
  `USU_CODIGO` int NOT NULL,
  `MEDP_DATACADASTRO` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_mmleitura`
--

CREATE TABLE `t_mmleitura` (
  `MML_CODIGO` int NOT NULL,
  `MOV_CODIGO` int NOT NULL,
  `MML_MEDIA` float NOT NULL,
  `MML_MINIMO` float NOT NULL,
  `MML_LEITURA` varchar(100) NOT NULL,
  `VEI_CODIGO` int DEFAULT NULL,
  `PNE_CODIGO` int NOT NULL,
  `INSP_CODIGO` int DEFAULT NULL,
  `SULCO_INTERNO` float NOT NULL,
  `SULCO_CENTRAL_INTERNO` float NOT NULL,
  `SULCO_CENTRAL_EXTERNO` float NOT NULL,
  `SULCO_EXTERNO` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_modelopneu`
--

CREATE TABLE `t_modelopneu` (
  `MODP_CODIGO` int NOT NULL,
  `MODP_DESCRICAO` varchar(30) NOT NULL,
  `MODP_STATUS` varchar(2) NOT NULL,
  `USU_CODIGO` int DEFAULT NULL,
  `MODP_DATACADASTRO` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `MARP_CODIGO` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_modeloveiculo`
--

CREATE TABLE `t_modeloveiculo` (
  `MODV_CODIGO` int NOT NULL,
  `MODV_DESCRICAO` varchar(30) NOT NULL,
  `MODV_STATUS` varchar(2) NOT NULL,
  `MARV_CODIGO` int NOT NULL,
  `VEIC_TIPO` varchar(2) NOT NULL,
  `USU_CODIGO` int NOT NULL,
  `MODV_DATACADASTRO` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_motivopai`
--

CREATE TABLE `t_motivopai` (
  `MOTPAI_CODIGO` int NOT NULL,
  `MOTPAI_DESCRICAO` varchar(30) NOT NULL,
  `MOTPAI_STATUS` varchar(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_motivopneu`
--

CREATE TABLE `t_motivopneu` (
  `MOTP_CODIGO` int NOT NULL,
  `MOTP_DESCRICAO` varchar(50) NOT NULL,
  `MOTP_STATUS` varchar(2) NOT NULL,
  `USU_CODIGO` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_motivoxpai`
--

CREATE TABLE `t_motivoxpai` (
  `MOPA_CODIGO` int NOT NULL,
  `MOTP_CODIGO` int NOT NULL,
  `MOTPAI_CODIGO` int NOT NULL,
  `MOPA_STATUS` varchar(1) NOT NULL DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_movimentacao`
--

CREATE TABLE `t_movimentacao` (
  `MOV_CODIGO` int NOT NULL,
  `PNE_CODIGO` int DEFAULT NULL,
  `PNEU_VIDA_ATUAL` varchar(2) DEFAULT NULL,
  `MOV_OPERACAO` varchar(2) NOT NULL,
  `MOV_MM_MINIMA` float DEFAULT NULL,
  `UNI_CODIGO` int DEFAULT NULL,
  `MOV_DATA` date NOT NULL,
  `MOV_DATAMOVIMENTO` timestamp NULL DEFAULT NULL,
  `VEI_CODIGO` int DEFAULT NULL,
  `USU_CODIGO` int DEFAULT NULL,
  `POS_CODIGO` int DEFAULT NULL,
  `MOV_KMVEICULO` int DEFAULT NULL,
  `MOV_KMPNEU` int DEFAULT NULL,
  `MOV_COMENTARIO` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_nfcomprapneus`
--

CREATE TABLE `t_nfcomprapneus` (
  `NF_CODIGO` int NOT NULL,
  `NF_NUM` int NOT NULL,
  `FORN_CODIGO` int NOT NULL,
  `UNI_CODIGO` int NOT NULL,
  `NF_DATA` date DEFAULT NULL,
  `NF_DATA_RECEBIMENTO` date DEFAULT NULL,
  `NF_CADASTRODATA` timestamp NULL DEFAULT NULL,
  `NF_TIPO` varchar(1) NOT NULL,
  `NF_VLTOTAL` double NOT NULL,
  `USU_CODIGO` int DEFAULT NULL,
  `NF_STATUS` char(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_pneu`
--

CREATE TABLE `t_pneu` (
  `PNE_CODIGO` int NOT NULL,
  `PNE_FOGO` varchar(20) NOT NULL,
  `TIPO_CODIGO` int NOT NULL,
  `CAL_RECOMENDADA` double DEFAULT NULL,
  `PNE_DOT` varchar(5) DEFAULT NULL,
  `PNE_KM` int NOT NULL DEFAULT '0',
  `PNE_MM` float NOT NULL,
  `PNE_STATUS` varchar(2) NOT NULL,
  `PNE_STATUSCOMPRA` varchar(1) NOT NULL,
  `PNE_VALORCOMPRA` double NOT NULL,
  `PNE_VIDACOMPRA` varchar(3) NOT NULL,
  `PNE_VIDAATUAL` varchar(3) NOT NULL,
  `TIPO_CODIGORECAPE` int NOT NULL,
  `ITS_CODIGO` int DEFAULT NULL,
  `PNE_CUSTOATUAL` double NOT NULL DEFAULT '0',
  `USU_CODIGO` int DEFAULT NULL,
  `UNI_CODIGO` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_pneuatual`
--

CREATE TABLE `t_pneuatual` (
  `PNEA_CODIGO` int NOT NULL,
  `MOV_CODIGO` int DEFAULT NULL,
  `PNE_CODIGO` int DEFAULT NULL,
  `PNE_FOGO` varchar(20) DEFAULT NULL,
  `VEI_CODIGO` int NOT NULL,
  `POS_CODIGO` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_posicao`
--

CREATE TABLE `t_posicao` (
  `POS_CODIGO` int NOT NULL,
  `POS_DESCRICAO` varchar(50) NOT NULL,
  `POS_STATUS` varchar(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_posicaoxconfiguracao`
--

CREATE TABLE `t_posicaoxconfiguracao` (
  `PSCF_CODIGO` int NOT NULL,
  `VEIC_CODIGO` int NOT NULL,
  `POS_CODIGO` int NOT NULL,
  `PSCF_PAR` int DEFAULT NULL,
  `PSCF_EIXO` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_recebimento_transferenciapneus`
--

CREATE TABLE `t_recebimento_transferenciapneus` (
  `id` int NOT NULL,
  `unidadeRecebimento` int NOT NULL,
  `dataRecebimento` date NOT NULL,
  `dataCadastro` datetime DEFAULT NULL,
  `usuarioCadastro` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_retornopneu`
--

CREATE TABLE `t_retornopneu` (
  `RETPNE_CODIGO` int NOT NULL,
  `RETPNE_NDOC` double NOT NULL,
  `RETPNE_DATA` date NOT NULL,
  `RETPNE_DATALANCAMENTO` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `RETPNE_STATUS` varchar(2) NOT NULL,
  `UNI_CODIGO` int NOT NULL,
  `FORN_CODIGO` int NOT NULL,
  `USU_CODIGO` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_saida`
--

CREATE TABLE `t_saida` (
  `SAIDA_CODIGO` int NOT NULL,
  `UNI_CODIGO` int NOT NULL,
  `FORN_CODIGO` int NOT NULL,
  `SAIDA_NDOCUMENTO` int NOT NULL,
  `SAIDA_STATUS` varchar(2) NOT NULL,
  `SAIDA_DATA` date NOT NULL,
  `SAIDA_DATAPREVISAO` date NOT NULL,
  `USU_CODIGO` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_tipo`
--

CREATE TABLE `t_tipo` (
  `TIPO_CODIGO` int NOT NULL,
  `TIPO_STATUS` varchar(2) NOT NULL,
  `TIPO_DESCRICAO` varchar(40) NOT NULL,
  `TIPO_INSPECAO` varchar(2) NOT NULL,
  `MARP_CODIGO` int NOT NULL,
  `MODP_CODIGO` int NOT NULL,
  `MEDP_CODIGO` int NOT NULL,
  `TIPO_DESENHO` varchar(2) NOT NULL,
  `TIPO_NSULCO` int NOT NULL,
  `TIPO_MMSEGURANCA` float NOT NULL,
  `TIPO_MMNOVO` float NOT NULL,
  `TIPO_MMDESGEIXOS` float DEFAULT NULL,
  `TIPO_MMDESGPAR` float DEFAULT NULL,
  `USU_CODIGO` int NOT NULL,
  `TIPO_DATACADASTRO` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_transferenciapneus`
--

CREATE TABLE `t_transferenciapneus` (
  `id` int NOT NULL,
  `unidadeRemetente` int NOT NULL,
  `unidadeDestino` int NOT NULL,
  `dataEnvio` date NOT NULL,
  `dataCadastro` datetime NOT NULL,
  `usuarioCadastro` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_usuario`
--

CREATE TABLE `t_usuario` (
  `USU_CODIGO` int NOT NULL,
  `USU_CPF` varchar(12) NOT NULL,
  `USU_USERNAME` varchar(40) NOT NULL,
  `USU_NOME` varchar(25) NOT NULL,
  `USU_SOBRENOME` varchar(70) NOT NULL,
  `USU_STATUS` varchar(1) NOT NULL,
  `USU_TIPO` varchar(2) NOT NULL,
  `USU_EMAIL` varchar(40) NOT NULL,
  `USU_TELEFONE` varchar(30) NOT NULL,
  `USU_SENHA` varchar(100) NOT NULL,
  `USU_LOGO` varchar(60) DEFAULT NULL,
  `USU_UNI_LOCAL` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_usuario_unidades`
--

CREATE TABLE `t_usuario_unidades` (
  `USXUN_CODIGO` int NOT NULL,
  `USU_CODIGO` int NOT NULL,
  `UNI_CODIGO` int NOT NULL,
  `USXUN_STATUS` varchar(2) NOT NULL,
  `USXUN_DATACADASTRO` datetime NOT NULL,
  `USU_CADASTRO` int NOT NULL,
  `USXUN_OBSERVACAO` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_veiculo`
--

CREATE TABLE `t_veiculo` (
  `VEI_CODIGO` int NOT NULL,
  `VEI_PLACA` varchar(7) NOT NULL,
  `VEI_CHASSI` varchar(17) DEFAULT NULL,
  `VEI_FROTA` varchar(25) DEFAULT NULL,
  `VEI_STATUS` varchar(2) NOT NULL,
  `CAL_RECOMENDADA` int DEFAULT NULL,
  `MODV_CODIGO` int NOT NULL,
  `UNI_CODIGO` int NOT NULL,
  `VEIC_CODIGO` int NOT NULL,
  `VEI_KM` int NOT NULL,
  `USU_CODIGO` int NOT NULL,
  `VEI_DATACADASTRO` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `VEI_OBS` tinytext,
  `VEI_ODOMETRO` varchar(2) NOT NULL,
  `USU_MOTORISTA` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_veiculoconfiguracao`
--

CREATE TABLE `t_veiculoconfiguracao` (
  `VEIC_CODIGO` int NOT NULL,
  `VEIC_DESCRICAO` varchar(50) NOT NULL,
  `VEIC_STATUS` varchar(2) NOT NULL,
  `VEIC_TIPO` varchar(2) NOT NULL,
  `VEIC_IMAGEM` varchar(100) NOT NULL,
  `VEIC_IMG_LARGURA` int NOT NULL,
  `VEIC_IMG_ALTURA` int NOT NULL,
  `VEIC_MARGIN_TOP` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura para tabela `t_vidapneu`
--

CREATE TABLE `t_vidapneu` (
  `VIPN_CODIGO` int NOT NULL,
  `PNE_CODIGO` int NOT NULL,
  `VIPN_VIDA` varchar(2) NOT NULL,
  `VIPN_KM` int NOT NULL,
  `VIPN_MM` int NOT NULL,
  `TIPO_CODIGO` int NOT NULL,
  `VIPN_CUSTO` float NOT NULL,
  `RETPNE_CODIGO` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `t_acessousuario`
--
ALTER TABLE `t_acessousuario`
  ADD PRIMARY KEY (`ACE_CODIGO`),
  ADD KEY `USU_CODIGOACESSO` (`USU_CODIGOACESSO`),
  ADD KEY `USU_CODIGOCADASTRO` (`USU_CODIGOCADASTRO`);

--
-- Índices de tabela `t_alocacaoveiculo`
--
ALTER TABLE `t_alocacaoveiculo`
  ADD PRIMARY KEY (`ALOC_CODIGO`);

--
-- Índices de tabela `t_atualizacao`
--
ALTER TABLE `t_atualizacao`
  ADD PRIMARY KEY (`ATU_CODIGO`);

--
-- Índices de tabela `t_auditoria_detalhes`
--
ALTER TABLE `t_auditoria_detalhes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_id` (`audit_id`),
  ADD KEY `idx_pne_fogo` (`pne_fogo`),
  ADD KEY `idx_pne_codigo` (`pne_codigo`),
  ADD KEY `idx_status_esperado` (`status_esperado`),
  ADD KEY `idx_status_encontrado` (`status_encontrado`),
  ADD KEY `idx_tipo_divergencia` (`tipo_divergencia`);

--
-- Índices de tabela `t_auditoria_estoque_status`
--
ALTER TABLE `t_auditoria_estoque_status`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_id` (`audit_id`);

--
-- Índices de tabela `t_auditoria_relatorio`
--
ALTER TABLE `t_auditoria_relatorio`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_audit` (`audit_id`),
  ADD KEY `idx_conf_usu` (`conf_usu_codigo`);

--
-- Índices de tabela `t_auditoria_sessao`
--
ALTER TABLE `t_auditoria_sessao`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_uni_codigo` (`uni_codigo`),
  ADD KEY `idx_usu_codigo` (`usu_codigo`),
  ADD KEY `idx_resultado` (`resultado`),
  ADD KEY `idx_conf_status` (`conf_status`),
  ADD KEY `idx_conf_usu_codigo` (`conf_usu_codigo`),
  ADD KEY `idx_conf_data_inicio` (`conf_data_inicio`),
  ADD KEY `idx_conf_data_fim` (`conf_data_fim`);

--
-- Índices de tabela `t_baixapneu`
--
ALTER TABLE `t_baixapneu`
  ADD PRIMARY KEY (`BAI_CODIGO`),
  ADD KEY `PNE_CODIGO` (`PNE_CODIGO`),
  ADD KEY `MOPA_CODIGO` (`MOPA_CODIGO`),
  ADD KEY `idx_baixapneu_status` (`BAI_STATUS`),
  ADD KEY `idx_baixapneu_pne_status` (`PNE_CODIGO`,`BAI_STATUS`);

--
-- Índices de tabela `t_calibragem`
--
ALTER TABLE `t_calibragem`
  ADD PRIMARY KEY (`CAL_CODIGO`),
  ADD KEY `INSP_CODIGO` (`INSP_CODIGO`),
  ADD KEY `MOV_CODIGO` (`MOV_CODIGO`),
  ADD KEY `USU_CODIGO` (`USU_CODIGO`),
  ADD KEY `VEI_CODIGO` (`VEI_CODIGO`),
  ADD KEY `PNE_CODIGO` (`PNE_CODIGO`);

--
-- Índices de tabela `t_cliente`
--
ALTER TABLE `t_cliente`
  ADD PRIMARY KEY (`CLI_CODIGO`);

--
-- Índices de tabela `t_clientepreferencia`
--
ALTER TABLE `t_clientepreferencia`
  ADD PRIMARY KEY (`PREF_CODIGO`);

--
-- Índices de tabela `t_clienteunidade`
--
ALTER TABLE `t_clienteunidade`
  ADD PRIMARY KEY (`UNI_CODIGO`);

--
-- Índices de tabela `t_fornecedor`
--
ALTER TABLE `t_fornecedor`
  ADD UNIQUE KEY `FORN_CODIGO` (`FORN_CODIGO`),
  ADD KEY `USU_CODIGO` (`USU_CODIGO`);

--
-- Índices de tabela `t_inspecao`
--
ALTER TABLE `t_inspecao`
  ADD PRIMARY KEY (`INSP_CODIGO`),
  ADD KEY `USU_CODIGO` (`USU_CODIGO`),
  ADD KEY `VEI_CODIGO` (`VEI_CODIGO`);

--
-- Índices de tabela `t_itensnfcomprapneus`
--
ALTER TABLE `t_itensnfcomprapneus`
  ADD UNIQUE KEY `ITS_ID` (`ITS_CODIGO`),
  ADD KEY `NF_CODIGO` (`NF_CODIGO`),
  ADD KEY `UNI_CODIGO` (`UNI_CODIGO`),
  ADD KEY `TIPO_CODIGO` (`TIPO_CODIGO`);

--
-- Índices de tabela `t_itensretorno`
--
ALTER TABLE `t_itensretorno`
  ADD PRIMARY KEY (`ITRT_CODIGO`),
  ADD KEY `PNE_CODIGO` (`PNE_CODIGO`),
  ADD KEY `TIPO_CODIGO` (`TIPO_CODIGO`),
  ADD KEY `RETPNE_CODIGO` (`RETPNE_CODIGO`);

--
-- Índices de tabela `t_itenssaida`
--
ALTER TABLE `t_itenssaida`
  ADD PRIMARY KEY (`ITSD_CODIGO`),
  ADD KEY `FORN_CODIGO` (`FORN_CODIGO`),
  ADD KEY `PNE_CODIGO` (`PNE_CODIGO`),
  ADD KEY `SAIDA_CODIGO` (`SAIDA_CODIGO`);

--
-- Índices de tabela `t_itens_recebimento`
--
ALTER TABLE `t_itens_recebimento`
  ADD PRIMARY KEY (`idItem`);

--
-- Índices de tabela `t_itens_transferenciapneus`
--
ALTER TABLE `t_itens_transferenciapneus`
  ADD PRIMARY KEY (`idItem`),
  ADD KEY `FK_TransferenciaPneu` (`idTransferencia`),
  ADD KEY `FK_PNECODIGO_TRANSF` (`pneuCodigo`),
  ADD KEY `FK_USUARIO_RETORNO_TRANS` (`usuarioRetorno`);

--
-- Índices de tabela `t_log_usuario`
--
ALTER TABLE `t_log_usuario`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_usu_data` (`usu_codigo`,`login_time`),
  ADD KEY `idx_session` (`session_id`);

--
-- Índices de tabela `t_marcapneu`
--
ALTER TABLE `t_marcapneu`
  ADD PRIMARY KEY (`MARP_CODIGO`),
  ADD KEY `MARP_USERCADASTRO` (`USU_CODIGO`);

--
-- Índices de tabela `t_marcaveiculo`
--
ALTER TABLE `t_marcaveiculo`
  ADD PRIMARY KEY (`MARV_CODIGO`),
  ADD KEY `USU_CODIGO` (`USU_CODIGO`);

--
-- Índices de tabela `t_medidapneu`
--
ALTER TABLE `t_medidapneu`
  ADD PRIMARY KEY (`MEDP_CODIGO`),
  ADD KEY `MEDP_USERCADASTRO` (`USU_CODIGO`);

--
-- Índices de tabela `t_mmleitura`
--
ALTER TABLE `t_mmleitura`
  ADD PRIMARY KEY (`MML_CODIGO`),
  ADD KEY `INSP_CODIGO` (`INSP_CODIGO`),
  ADD KEY `MOV_CODIGO` (`MOV_CODIGO`),
  ADD KEY `PNE_CODIGO` (`PNE_CODIGO`),
  ADD KEY `VEI_CODIGO` (`VEI_CODIGO`);

--
-- Índices de tabela `t_modelopneu`
--
ALTER TABLE `t_modelopneu`
  ADD PRIMARY KEY (`MODP_CODIGO`),
  ADD KEY `MARP_CODIGO` (`MARP_CODIGO`),
  ADD KEY `MODP_USERCADASTRO` (`USU_CODIGO`);

--
-- Índices de tabela `t_modeloveiculo`
--
ALTER TABLE `t_modeloveiculo`
  ADD PRIMARY KEY (`MODV_CODIGO`),
  ADD KEY `MARV_CODIGO` (`MARV_CODIGO`),
  ADD KEY `USU_CODIGO` (`USU_CODIGO`);

--
-- Índices de tabela `t_motivopai`
--
ALTER TABLE `t_motivopai`
  ADD PRIMARY KEY (`MOTPAI_CODIGO`);

--
-- Índices de tabela `t_motivopneu`
--
ALTER TABLE `t_motivopneu`
  ADD PRIMARY KEY (`MOTP_CODIGO`),
  ADD KEY `USU_CODIGO` (`USU_CODIGO`);

--
-- Índices de tabela `t_motivoxpai`
--
ALTER TABLE `t_motivoxpai`
  ADD PRIMARY KEY (`MOPA_CODIGO`),
  ADD KEY `MOTPAI_CODIGO` (`MOTPAI_CODIGO`),
  ADD KEY `MOTP_CODIGO` (`MOTP_CODIGO`);

--
-- Índices de tabela `t_movimentacao`
--
ALTER TABLE `t_movimentacao`
  ADD PRIMARY KEY (`MOV_CODIGO`),
  ADD KEY `PNE_CODIGO` (`PNE_CODIGO`),
  ADD KEY `POS_CODIGO` (`POS_CODIGO`),
  ADD KEY `USU_CODIGO` (`USU_CODIGO`),
  ADD KEY `VEI_CODIGO` (`VEI_CODIGO`);

--
-- Índices de tabela `t_nfcomprapneus`
--
ALTER TABLE `t_nfcomprapneus`
  ADD PRIMARY KEY (`NF_CODIGO`),
  ADD UNIQUE KEY `NF_ID` (`NF_CODIGO`),
  ADD KEY `FORN_CODIGO` (`FORN_CODIGO`),
  ADD KEY `USU_CODIGO` (`USU_CODIGO`);

--
-- Índices de tabela `t_pneu`
--
ALTER TABLE `t_pneu`
  ADD PRIMARY KEY (`PNE_CODIGO`,`PNE_FOGO`),
  ADD KEY `COD_USUARIO` (`USU_CODIGO`),
  ADD KEY `UNI_CODIGO` (`UNI_CODIGO`),
  ADD KEY `TIPO_CODIGO` (`TIPO_CODIGO`),
  ADD KEY `ITS_CODIGO` (`ITS_CODIGO`) USING BTREE;

--
-- Índices de tabela `t_pneuatual`
--
ALTER TABLE `t_pneuatual`
  ADD PRIMARY KEY (`PNEA_CODIGO`),
  ADD KEY `MOV_CODIGO` (`MOV_CODIGO`),
  ADD KEY `PNE_CODIGO` (`PNE_CODIGO`),
  ADD KEY `POS_CODIGO` (`POS_CODIGO`),
  ADD KEY `VEI_CODIGO` (`VEI_CODIGO`),
  ADD KEY `atual_PNEFOGO` (`PNE_FOGO`(10));

--
-- Índices de tabela `t_posicao`
--
ALTER TABLE `t_posicao`
  ADD PRIMARY KEY (`POS_CODIGO`);

--
-- Índices de tabela `t_posicaoxconfiguracao`
--
ALTER TABLE `t_posicaoxconfiguracao`
  ADD PRIMARY KEY (`PSCF_CODIGO`),
  ADD KEY `t_posicaoxconfiguracao_PK_ibfk_1` (`POS_CODIGO`),
  ADD KEY `t_posicaoxconfiguracao_PK_ibfk_2` (`VEIC_CODIGO`);

--
-- Índices de tabela `t_recebimento_transferenciapneus`
--
ALTER TABLE `t_recebimento_transferenciapneus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `unidadeRecebimento` (`unidadeRecebimento`),
  ADD KEY `usuarioCadastro` (`usuarioCadastro`);

--
-- Índices de tabela `t_retornopneu`
--
ALTER TABLE `t_retornopneu`
  ADD PRIMARY KEY (`RETPNE_CODIGO`),
  ADD KEY `FORN_CODIGO` (`FORN_CODIGO`),
  ADD KEY `UNI_CODIGO` (`UNI_CODIGO`),
  ADD KEY `USU_CODIGO` (`USU_CODIGO`);

--
-- Índices de tabela `t_saida`
--
ALTER TABLE `t_saida`
  ADD PRIMARY KEY (`SAIDA_CODIGO`),
  ADD KEY `FORN_CODIGO` (`FORN_CODIGO`),
  ADD KEY `UNI_CODIGO` (`UNI_CODIGO`),
  ADD KEY `USU_CODIGO` (`USU_CODIGO`);

--
-- Índices de tabela `t_tipo`
--
ALTER TABLE `t_tipo`
  ADD PRIMARY KEY (`TIPO_CODIGO`),
  ADD KEY `MARP_CODIGO` (`MARP_CODIGO`),
  ADD KEY `MODP_CODIGO` (`MODP_CODIGO`),
  ADD KEY `USU_CODIGO` (`USU_CODIGO`),
  ADD KEY `MEDP_CODIGO` (`MEDP_CODIGO`);

--
-- Índices de tabela `t_transferenciapneus`
--
ALTER TABLE `t_transferenciapneus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_UNI_REMETENTE` (`unidadeRemetente`),
  ADD KEY `FK_UNI_DESTINO` (`unidadeDestino`),
  ADD KEY `FK_USUARIO_CADASTRO` (`usuarioCadastro`);

--
-- Índices de tabela `t_usuario`
--
ALTER TABLE `t_usuario`
  ADD PRIMARY KEY (`USU_CODIGO`,`USU_CPF`);

--
-- Índices de tabela `t_usuario_unidades`
--
ALTER TABLE `t_usuario_unidades`
  ADD PRIMARY KEY (`USXUN_CODIGO`);

--
-- Índices de tabela `t_veiculo`
--
ALTER TABLE `t_veiculo`
  ADD PRIMARY KEY (`VEI_CODIGO`),
  ADD KEY `MODV_CODIGO` (`MODV_CODIGO`),
  ADD KEY `USU_CODIGO` (`USU_CODIGO`),
  ADD KEY `UNI_CODIGO` (`UNI_CODIGO`),
  ADD KEY `VEIC_CODIGO` (`VEIC_CODIGO`);

--
-- Índices de tabela `t_veiculoconfiguracao`
--
ALTER TABLE `t_veiculoconfiguracao`
  ADD PRIMARY KEY (`VEIC_CODIGO`);

--
-- Índices de tabela `t_vidapneu`
--
ALTER TABLE `t_vidapneu`
  ADD PRIMARY KEY (`VIPN_CODIGO`),
  ADD KEY `PNE_CODIGO` (`PNE_CODIGO`),
  ADD KEY `TIPO_CODIGO` (`TIPO_CODIGO`),
  ADD KEY `RETPNE_CODIGO` (`RETPNE_CODIGO`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `t_acessousuario`
--
ALTER TABLE `t_acessousuario`
  MODIFY `ACE_CODIGO` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_alocacaoveiculo`
--
ALTER TABLE `t_alocacaoveiculo`
  MODIFY `ALOC_CODIGO` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_atualizacao`
--
ALTER TABLE `t_atualizacao`
  MODIFY `ATU_CODIGO` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_auditoria_detalhes`
--
ALTER TABLE `t_auditoria_detalhes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_auditoria_estoque_status`
--
ALTER TABLE `t_auditoria_estoque_status`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_auditoria_relatorio`
--
ALTER TABLE `t_auditoria_relatorio`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_auditoria_sessao`
--
ALTER TABLE `t_auditoria_sessao`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_baixapneu`
--
ALTER TABLE `t_baixapneu`
  MODIFY `BAI_CODIGO` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_calibragem`
--
ALTER TABLE `t_calibragem`
  MODIFY `CAL_CODIGO` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_cliente`
--
ALTER TABLE `t_cliente`
  MODIFY `CLI_CODIGO` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_clientepreferencia`
--
ALTER TABLE `t_clientepreferencia`
  MODIFY `PREF_CODIGO` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_clienteunidade`
--
ALTER TABLE `t_clienteunidade`
  MODIFY `UNI_CODIGO` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_fornecedor`
--
ALTER TABLE `t_fornecedor`
  MODIFY `FORN_CODIGO` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_inspecao`
--
ALTER TABLE `t_inspecao`
  MODIFY `INSP_CODIGO` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_itensnfcomprapneus`
--
ALTER TABLE `t_itensnfcomprapneus`
  MODIFY `ITS_CODIGO` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_itensretorno`
--
ALTER TABLE `t_itensretorno`
  MODIFY `ITRT_CODIGO` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_itenssaida`
--
ALTER TABLE `t_itenssaida`
  MODIFY `ITSD_CODIGO` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_itens_recebimento`
--
ALTER TABLE `t_itens_recebimento`
  MODIFY `idItem` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_itens_transferenciapneus`
--
ALTER TABLE `t_itens_transferenciapneus`
  MODIFY `idItem` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_log_usuario`
--
ALTER TABLE `t_log_usuario`
  MODIFY `log_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_marcapneu`
--
ALTER TABLE `t_marcapneu`
  MODIFY `MARP_CODIGO` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_marcaveiculo`
--
ALTER TABLE `t_marcaveiculo`
  MODIFY `MARV_CODIGO` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_medidapneu`
--
ALTER TABLE `t_medidapneu`
  MODIFY `MEDP_CODIGO` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_mmleitura`
--
ALTER TABLE `t_mmleitura`
  MODIFY `MML_CODIGO` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_modelopneu`
--
ALTER TABLE `t_modelopneu`
  MODIFY `MODP_CODIGO` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_modeloveiculo`
--
ALTER TABLE `t_modeloveiculo`
  MODIFY `MODV_CODIGO` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_motivopai`
--
ALTER TABLE `t_motivopai`
  MODIFY `MOTPAI_CODIGO` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_motivopneu`
--
ALTER TABLE `t_motivopneu`
  MODIFY `MOTP_CODIGO` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_motivoxpai`
--
ALTER TABLE `t_motivoxpai`
  MODIFY `MOPA_CODIGO` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_movimentacao`
--
ALTER TABLE `t_movimentacao`
  MODIFY `MOV_CODIGO` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_nfcomprapneus`
--
ALTER TABLE `t_nfcomprapneus`
  MODIFY `NF_CODIGO` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_pneu`
--
ALTER TABLE `t_pneu`
  MODIFY `PNE_CODIGO` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_pneuatual`
--
ALTER TABLE `t_pneuatual`
  MODIFY `PNEA_CODIGO` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_recebimento_transferenciapneus`
--
ALTER TABLE `t_recebimento_transferenciapneus`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_retornopneu`
--
ALTER TABLE `t_retornopneu`
  MODIFY `RETPNE_CODIGO` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_saida`
--
ALTER TABLE `t_saida`
  MODIFY `SAIDA_CODIGO` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_tipo`
--
ALTER TABLE `t_tipo`
  MODIFY `TIPO_CODIGO` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_transferenciapneus`
--
ALTER TABLE `t_transferenciapneus`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_usuario`
--
ALTER TABLE `t_usuario`
  MODIFY `USU_CODIGO` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_usuario_unidades`
--
ALTER TABLE `t_usuario_unidades`
  MODIFY `USXUN_CODIGO` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_veiculo`
--
ALTER TABLE `t_veiculo`
  MODIFY `VEI_CODIGO` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `t_vidapneu`
--
ALTER TABLE `t_vidapneu`
  MODIFY `VIPN_CODIGO` int NOT NULL AUTO_INCREMENT;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `t_auditoria_detalhes`
--
ALTER TABLE `t_auditoria_detalhes`
  ADD CONSTRAINT `fk_audit_sessao` FOREIGN KEY (`audit_id`) REFERENCES `t_auditoria_sessao` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `t_auditoria_estoque_status`
--
ALTER TABLE `t_auditoria_estoque_status`
  ADD CONSTRAINT `fk_audit_ref` FOREIGN KEY (`audit_id`) REFERENCES `t_auditoria_sessao` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `t_auditoria_relatorio`
--
ALTER TABLE `t_auditoria_relatorio`
  ADD CONSTRAINT `fk_rel_audit` FOREIGN KEY (`audit_id`) REFERENCES `t_auditoria_sessao` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `t_baixapneu`
--
ALTER TABLE `t_baixapneu`
  ADD CONSTRAINT `t_baixapneu_ibfk_1` FOREIGN KEY (`PNE_CODIGO`) REFERENCES `t_pneu` (`PNE_CODIGO`),
  ADD CONSTRAINT `t_baixapneu_ibfk_2` FOREIGN KEY (`MOPA_CODIGO`) REFERENCES `t_motivoxpai` (`MOPA_CODIGO`);

--
-- Restrições para tabelas `t_calibragem`
--
ALTER TABLE `t_calibragem`
  ADD CONSTRAINT `t_calibragem_ibfk_1` FOREIGN KEY (`MOV_CODIGO`) REFERENCES `t_movimentacao` (`MOV_CODIGO`),
  ADD CONSTRAINT `t_calibragem_ibfk_2` FOREIGN KEY (`VEI_CODIGO`) REFERENCES `t_veiculo` (`VEI_CODIGO`),
  ADD CONSTRAINT `t_calibragem_ibfk_3` FOREIGN KEY (`PNE_CODIGO`) REFERENCES `t_pneu` (`PNE_CODIGO`),
  ADD CONSTRAINT `t_calibragem_ibfk_4` FOREIGN KEY (`INSP_CODIGO`) REFERENCES `t_inspecao` (`INSP_CODIGO`),
  ADD CONSTRAINT `t_calibragem_ibfk_5` FOREIGN KEY (`USU_CODIGO`) REFERENCES `t_usuario` (`USU_CODIGO`);

--
-- Restrições para tabelas `t_fornecedor`
--
ALTER TABLE `t_fornecedor`
  ADD CONSTRAINT `t_fornecedor_ibfk_1` FOREIGN KEY (`USU_CODIGO`) REFERENCES `t_usuario` (`USU_CODIGO`);

--
-- Restrições para tabelas `t_inspecao`
--
ALTER TABLE `t_inspecao`
  ADD CONSTRAINT `t_inspecao_ibfk_1` FOREIGN KEY (`VEI_CODIGO`) REFERENCES `t_veiculo` (`VEI_CODIGO`),
  ADD CONSTRAINT `t_inspecao_ibfk_2` FOREIGN KEY (`USU_CODIGO`) REFERENCES `t_usuario` (`USU_CODIGO`);

--
-- Restrições para tabelas `t_itensnfcomprapneus`
--
ALTER TABLE `t_itensnfcomprapneus`
  ADD CONSTRAINT `t_itensnfcomprapneus_ibfk_1` FOREIGN KEY (`NF_CODIGO`) REFERENCES `t_nfcomprapneus` (`NF_CODIGO`),
  ADD CONSTRAINT `t_itensnfcomprapneus_ibfk_2` FOREIGN KEY (`UNI_CODIGO`) REFERENCES `t_clienteunidade` (`UNI_CODIGO`),
  ADD CONSTRAINT `t_itensnfcomprapneus_ibfk_3` FOREIGN KEY (`TIPO_CODIGO`) REFERENCES `t_tipo` (`TIPO_CODIGO`);

--
-- Restrições para tabelas `t_itensretorno`
--
ALTER TABLE `t_itensretorno`
  ADD CONSTRAINT `t_itensretorno_ibfk_1` FOREIGN KEY (`RETPNE_CODIGO`) REFERENCES `t_retornopneu` (`RETPNE_CODIGO`),
  ADD CONSTRAINT `t_itensretorno_ibfk_2` FOREIGN KEY (`PNE_CODIGO`) REFERENCES `t_pneu` (`PNE_CODIGO`),
  ADD CONSTRAINT `t_itensretorno_ibfk_3` FOREIGN KEY (`TIPO_CODIGO`) REFERENCES `t_tipo` (`TIPO_CODIGO`);

--
-- Restrições para tabelas `t_itenssaida`
--
ALTER TABLE `t_itenssaida`
  ADD CONSTRAINT `t_itenssaida_ibfk_1` FOREIGN KEY (`SAIDA_CODIGO`) REFERENCES `t_saida` (`SAIDA_CODIGO`),
  ADD CONSTRAINT `t_itenssaida_ibfk_2` FOREIGN KEY (`PNE_CODIGO`) REFERENCES `t_pneu` (`PNE_CODIGO`),
  ADD CONSTRAINT `t_itenssaida_ibfk_3` FOREIGN KEY (`FORN_CODIGO`) REFERENCES `t_fornecedor` (`FORN_CODIGO`);

--
-- Restrições para tabelas `t_itens_transferenciapneus`
--
ALTER TABLE `t_itens_transferenciapneus`
  ADD CONSTRAINT `FK_PNECODIGO_TRANSF` FOREIGN KEY (`pneuCodigo`) REFERENCES `t_pneu` (`PNE_CODIGO`),
  ADD CONSTRAINT `FK_TransferenciaPneu` FOREIGN KEY (`idTransferencia`) REFERENCES `t_transferenciapneus` (`id`),
  ADD CONSTRAINT `FK_USUARIO_RETORNO_TRANS` FOREIGN KEY (`usuarioRetorno`) REFERENCES `t_usuario` (`USU_CODIGO`);

--
-- Restrições para tabelas `t_log_usuario`
--
ALTER TABLE `t_log_usuario`
  ADD CONSTRAINT `t_log_usuario_ibfk_1` FOREIGN KEY (`usu_codigo`) REFERENCES `t_usuario` (`USU_CODIGO`) ON DELETE CASCADE;

--
-- Restrições para tabelas `t_marcapneu`
--
ALTER TABLE `t_marcapneu`
  ADD CONSTRAINT `t_marcapneu_ibfk_1` FOREIGN KEY (`USU_CODIGO`) REFERENCES `t_usuario` (`USU_CODIGO`);

--
-- Restrições para tabelas `t_marcaveiculo`
--
ALTER TABLE `t_marcaveiculo`
  ADD CONSTRAINT `t_marcaveiculo_ibfk_1` FOREIGN KEY (`USU_CODIGO`) REFERENCES `t_usuario` (`USU_CODIGO`);

--
-- Restrições para tabelas `t_medidapneu`
--
ALTER TABLE `t_medidapneu`
  ADD CONSTRAINT `t_medidapneu_ibfk_1` FOREIGN KEY (`USU_CODIGO`) REFERENCES `t_usuario` (`USU_CODIGO`);

--
-- Restrições para tabelas `t_mmleitura`
--
ALTER TABLE `t_mmleitura`
  ADD CONSTRAINT `t_mmleitura_ibfk_1` FOREIGN KEY (`MOV_CODIGO`) REFERENCES `t_movimentacao` (`MOV_CODIGO`),
  ADD CONSTRAINT `t_mmleitura_ibfk_2` FOREIGN KEY (`VEI_CODIGO`) REFERENCES `t_veiculo` (`VEI_CODIGO`),
  ADD CONSTRAINT `t_mmleitura_ibfk_3` FOREIGN KEY (`PNE_CODIGO`) REFERENCES `t_pneu` (`PNE_CODIGO`),
  ADD CONSTRAINT `t_mmleitura_ibfk_4` FOREIGN KEY (`INSP_CODIGO`) REFERENCES `t_inspecao` (`INSP_CODIGO`);

--
-- Restrições para tabelas `t_modelopneu`
--
ALTER TABLE `t_modelopneu`
  ADD CONSTRAINT `t_modelopneu_ibfk_1` FOREIGN KEY (`MARP_CODIGO`) REFERENCES `t_marcapneu` (`MARP_CODIGO`),
  ADD CONSTRAINT `t_modelopneu_ibfk_2` FOREIGN KEY (`USU_CODIGO`) REFERENCES `t_usuario` (`USU_CODIGO`);

--
-- Restrições para tabelas `t_modeloveiculo`
--
ALTER TABLE `t_modeloveiculo`
  ADD CONSTRAINT `t_modeloveiculo_ibfk_1` FOREIGN KEY (`MARV_CODIGO`) REFERENCES `t_marcaveiculo` (`MARV_CODIGO`),
  ADD CONSTRAINT `t_modeloveiculo_ibfk_2` FOREIGN KEY (`USU_CODIGO`) REFERENCES `t_usuario` (`USU_CODIGO`);

--
-- Restrições para tabelas `t_motivopneu`
--
ALTER TABLE `t_motivopneu`
  ADD CONSTRAINT `t_motivopneu_ibfk_1` FOREIGN KEY (`USU_CODIGO`) REFERENCES `t_usuario` (`USU_CODIGO`);

--
-- Restrições para tabelas `t_motivoxpai`
--
ALTER TABLE `t_motivoxpai`
  ADD CONSTRAINT `t_motivoxpai_ibfk_1` FOREIGN KEY (`MOTPAI_CODIGO`) REFERENCES `t_motivopai` (`MOTPAI_CODIGO`),
  ADD CONSTRAINT `t_motivoxpai_ibfk_2` FOREIGN KEY (`MOTP_CODIGO`) REFERENCES `t_motivopneu` (`MOTP_CODIGO`);

--
-- Restrições para tabelas `t_movimentacao`
--
ALTER TABLE `t_movimentacao`
  ADD CONSTRAINT `t_movimentacao_ibfk_1` FOREIGN KEY (`PNE_CODIGO`) REFERENCES `t_pneu` (`PNE_CODIGO`),
  ADD CONSTRAINT `t_movimentacao_ibfk_2` FOREIGN KEY (`POS_CODIGO`) REFERENCES `t_posicao` (`POS_CODIGO`),
  ADD CONSTRAINT `t_movimentacao_ibfk_3` FOREIGN KEY (`USU_CODIGO`) REFERENCES `t_usuario` (`USU_CODIGO`),
  ADD CONSTRAINT `t_movimentacao_ibfk_4` FOREIGN KEY (`VEI_CODIGO`) REFERENCES `t_veiculo` (`VEI_CODIGO`);

--
-- Restrições para tabelas `t_nfcomprapneus`
--
ALTER TABLE `t_nfcomprapneus`
  ADD CONSTRAINT `t_nfcomprapneus_ibfk_2` FOREIGN KEY (`FORN_CODIGO`) REFERENCES `t_fornecedor` (`FORN_CODIGO`),
  ADD CONSTRAINT `t_nfcomprapneus_ibfk_3` FOREIGN KEY (`USU_CODIGO`) REFERENCES `t_usuario` (`USU_CODIGO`);

--
-- Restrições para tabelas `t_pneu`
--
ALTER TABLE `t_pneu`
  ADD CONSTRAINT `t_pneu_ibfk_2` FOREIGN KEY (`USU_CODIGO`) REFERENCES `t_usuario` (`USU_CODIGO`),
  ADD CONSTRAINT `t_pneu_ibfk_3` FOREIGN KEY (`ITS_CODIGO`) REFERENCES `t_itensnfcomprapneus` (`ITS_CODIGO`),
  ADD CONSTRAINT `t_pneu_ibfk_4` FOREIGN KEY (`UNI_CODIGO`) REFERENCES `t_clienteunidade` (`UNI_CODIGO`),
  ADD CONSTRAINT `t_pneu_ibfk_5` FOREIGN KEY (`TIPO_CODIGO`) REFERENCES `t_tipo` (`TIPO_CODIGO`);

--
-- Restrições para tabelas `t_pneuatual`
--
ALTER TABLE `t_pneuatual`
  ADD CONSTRAINT `t_pneuatual_ibfk_1` FOREIGN KEY (`MOV_CODIGO`) REFERENCES `t_movimentacao` (`MOV_CODIGO`),
  ADD CONSTRAINT `t_pneuatual_ibfk_2` FOREIGN KEY (`PNE_CODIGO`) REFERENCES `t_pneu` (`PNE_CODIGO`),
  ADD CONSTRAINT `t_pneuatual_ibfk_3` FOREIGN KEY (`POS_CODIGO`) REFERENCES `t_posicao` (`POS_CODIGO`),
  ADD CONSTRAINT `t_pneuatual_ibfk_4` FOREIGN KEY (`VEI_CODIGO`) REFERENCES `t_veiculo` (`VEI_CODIGO`);

--
-- Restrições para tabelas `t_posicaoxconfiguracao`
--
ALTER TABLE `t_posicaoxconfiguracao`
  ADD CONSTRAINT `t_posicaoxconfiguracao_PK_ibfk_1` FOREIGN KEY (`POS_CODIGO`) REFERENCES `t_posicao` (`POS_CODIGO`),
  ADD CONSTRAINT `t_posicaoxconfiguracao_PK_ibfk_2` FOREIGN KEY (`VEIC_CODIGO`) REFERENCES `t_veiculoconfiguracao` (`VEIC_CODIGO`);

--
-- Restrições para tabelas `t_recebimento_transferenciapneus`
--
ALTER TABLE `t_recebimento_transferenciapneus`
  ADD CONSTRAINT `t_recebimento_transferenciapneus_ibfk_1` FOREIGN KEY (`unidadeRecebimento`) REFERENCES `t_clienteunidade` (`UNI_CODIGO`),
  ADD CONSTRAINT `t_recebimento_transferenciapneus_ibfk_2` FOREIGN KEY (`usuarioCadastro`) REFERENCES `t_usuario` (`USU_CODIGO`);

--
-- Restrições para tabelas `t_retornopneu`
--
ALTER TABLE `t_retornopneu`
  ADD CONSTRAINT `t_retornopneu_ibfk_1` FOREIGN KEY (`FORN_CODIGO`) REFERENCES `t_fornecedor` (`FORN_CODIGO`),
  ADD CONSTRAINT `t_retornopneu_ibfk_2` FOREIGN KEY (`UNI_CODIGO`) REFERENCES `t_clienteunidade` (`UNI_CODIGO`),
  ADD CONSTRAINT `t_retornopneu_ibfk_3` FOREIGN KEY (`USU_CODIGO`) REFERENCES `t_usuario` (`USU_CODIGO`);

--
-- Restrições para tabelas `t_saida`
--
ALTER TABLE `t_saida`
  ADD CONSTRAINT `t_saida_ibfk_1` FOREIGN KEY (`FORN_CODIGO`) REFERENCES `t_fornecedor` (`FORN_CODIGO`),
  ADD CONSTRAINT `t_saida_ibfk_2` FOREIGN KEY (`UNI_CODIGO`) REFERENCES `t_clienteunidade` (`UNI_CODIGO`),
  ADD CONSTRAINT `t_saida_ibfk_3` FOREIGN KEY (`USU_CODIGO`) REFERENCES `t_usuario` (`USU_CODIGO`);

--
-- Restrições para tabelas `t_tipo`
--
ALTER TABLE `t_tipo`
  ADD CONSTRAINT `t_tipo_ibfk_1` FOREIGN KEY (`MARP_CODIGO`) REFERENCES `t_marcapneu` (`MARP_CODIGO`),
  ADD CONSTRAINT `t_tipo_ibfk_2` FOREIGN KEY (`MODP_CODIGO`) REFERENCES `t_modelopneu` (`MODP_CODIGO`),
  ADD CONSTRAINT `t_tipo_ibfk_3` FOREIGN KEY (`MEDP_CODIGO`) REFERENCES `t_medidapneu` (`MEDP_CODIGO`),
  ADD CONSTRAINT `t_tipo_ibfk_4` FOREIGN KEY (`USU_CODIGO`) REFERENCES `t_usuario` (`USU_CODIGO`);

--
-- Restrições para tabelas `t_transferenciapneus`
--
ALTER TABLE `t_transferenciapneus`
  ADD CONSTRAINT `FK_UNI_DESTINO` FOREIGN KEY (`unidadeDestino`) REFERENCES `t_clienteunidade` (`UNI_CODIGO`),
  ADD CONSTRAINT `FK_UNI_REMETENTE` FOREIGN KEY (`unidadeRemetente`) REFERENCES `t_clienteunidade` (`UNI_CODIGO`),
  ADD CONSTRAINT `FK_USUARIO_CADASTRO` FOREIGN KEY (`usuarioCadastro`) REFERENCES `t_usuario` (`USU_CODIGO`);

--
-- Restrições para tabelas `t_veiculo`
--
ALTER TABLE `t_veiculo`
  ADD CONSTRAINT `t_veiculo_ibfk_1` FOREIGN KEY (`MODV_CODIGO`) REFERENCES `t_modeloveiculo` (`MODV_CODIGO`),
  ADD CONSTRAINT `t_veiculo_ibfk_2` FOREIGN KEY (`USU_CODIGO`) REFERENCES `t_usuario` (`USU_CODIGO`),
  ADD CONSTRAINT `t_veiculo_ibfk_3` FOREIGN KEY (`UNI_CODIGO`) REFERENCES `t_clienteunidade` (`UNI_CODIGO`),
  ADD CONSTRAINT `t_veiculo_ibfk_4` FOREIGN KEY (`VEIC_CODIGO`) REFERENCES `t_veiculoconfiguracao` (`VEIC_CODIGO`);

--
-- Restrições para tabelas `t_vidapneu`
--
ALTER TABLE `t_vidapneu`
  ADD CONSTRAINT `t_vidapneu_ibfk_1` FOREIGN KEY (`PNE_CODIGO`) REFERENCES `t_pneu` (`PNE_CODIGO`),
  ADD CONSTRAINT `t_vidapneu_ibfk_2` FOREIGN KEY (`TIPO_CODIGO`) REFERENCES `t_tipo` (`TIPO_CODIGO`),
  ADD CONSTRAINT `t_vidapneu_ibfk_3` FOREIGN KEY (`RETPNE_CODIGO`) REFERENCES `t_retornopneu` (`RETPNE_CODIGO`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
