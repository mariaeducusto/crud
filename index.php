<?php
require_once 'includes/conexao.php';

$pdo     = conectar();
$stmt    = $pdo->query("SELECT * FROM tarefas ORDER BY id DESC");
$tarefas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total     = count($tarefas);
$pendentes = count(array_filter($tarefas, fn($t) => $t['status'] === 'pendente'));
$concluidas = $total - $pendentes;

$mensagem = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Tarefas</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --azul:        #1a56db;
            --azul-hover:  #1648c0;
            --azul-light:  #ebf2ff;
            --verde:       #057a55;
            --verde-bg:    #def7ec;
            --amarelo:     #92400e;
            --amarelo-bg:  #fef3c7;
            --vermelho:    #c81e1e;
            --vermelho-bg: #fde8e8;
            --cinza-50:    #f9fafb;
            --cinza-100:   #f3f4f6;
            --cinza-200:   #e5e7eb;
            --cinza-400:   #9ca3af;
            --cinza-600:   #4b5563;
            --cinza-800:   #1f2937;
            --branco:      #ffffff;
            --sombra:      0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.05);
            --sombra-md:   0 4px 6px rgba(0,0,0,.07), 0 2px 4px rgba(0,0,0,.05);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--cinza-100);
            color: var(--cinza-800);
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: 220px;
            height: 100vh;
            background: var(--cinza-800);
            padding: 0;
            z-index: 100;
        }

        .sidebar-logo {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }

        .sidebar-logo span {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--branco);
            letter-spacing: -0.01em;
        }

        .sidebar-logo small {
            display: block;
            font-size: 0.7rem;
            color: var(--cinza-400);
            font-weight: 400;
            margin-top: 2px;
        }

        .sidebar-nav {
            padding: 16px 12px;
        }

        .nav-label {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--cinza-400);
            padding: 0 8px;
            margin-bottom: 6px;
            margin-top: 16px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 10px;
            border-radius: 6px;
            color: rgba(255,255,255,.6);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 400;
            transition: all 0.15s;
        }

        .nav-item:hover { background: rgba(255,255,255,.06); color: white; }
        .nav-item.ativo { background: var(--azul); color: white; font-weight: 500; }

        .nav-item svg { flex-shrink: 0; opacity: 0.8; }

        /* Layout principal */
        .main {
            margin-left: 220px;
            min-height: 100vh;
        }

        /* Topbar */
        .topbar {
            background: var(--branco);
            border-bottom: 1px solid var(--cinza-200);
            padding: 0 32px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .topbar h1 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--cinza-800);
        }

        .topbar-acoes { display: flex; gap: 10px; align-items: center; }

        /* Conteudo */
        .conteudo {
            padding: 28px 32px;
        }

        /* Cards de resumo */
        .resumo {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 28px;
        }

        .card-resumo {
            background: var(--branco);
            border: 1px solid var(--cinza-200);
            border-radius: 8px;
            padding: 20px 24px;
            box-shadow: var(--sombra);
        }

        .card-resumo .label {
            font-size: 0.75rem;
            color: var(--cinza-400);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .card-resumo .valor {
            font-size: 1.9rem;
            font-weight: 600;
            letter-spacing: -0.03em;
            line-height: 1;
        }

        .card-resumo .valor.azul    { color: var(--azul); }
        .card-resumo .valor.verde   { color: var(--verde); }
        .card-resumo .valor.amarelo { color: #d97706; }

        /* Painel da tabela */
        .painel {
            background: var(--branco);
            border: 1px solid var(--cinza-200);
            border-radius: 8px;
            box-shadow: var(--sombra);
            overflow: hidden;
        }

        .painel-header {
            padding: 16px 24px;
            border-bottom: 1px solid var(--cinza-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .painel-header h2 {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--cinza-800);
        }

        .painel-header small {
            font-size: 0.78rem;
            color: var(--cinza-400);
            font-weight: 400;
            margin-left: 8px;
        }

        /* Alerta */
        .alerta {
            margin: 16px 24px 0;
            background: var(--verde-bg);
            border: 1px solid #a7f3d0;
            color: var(--verde);
            padding: 10px 14px;
            border-radius: 6px;
            font-size: 0.83rem;
            font-weight: 500;
        }

        /* Tabela */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead tr {
            background: var(--cinza-50);
            border-bottom: 1px solid var(--cinza-200);
        }

        th {
            text-align: left;
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--cinza-400);
            text-transform: uppercase;
            letter-spacing: 0.07em;
            padding: 11px 16px;
        }

        tbody tr {
            border-bottom: 1px solid var(--cinza-100);
            transition: background 0.1s;
        }

        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: var(--cinza-50); }

        td {
            padding: 14px 16px;
            font-size: 0.875rem;
            vertical-align: middle;
        }

        .col-id {
            color: var(--cinza-400);
            font-size: 0.78rem;
            width: 50px;
            font-weight: 500;
        }

        .col-titulo strong {
            display: block;
            font-weight: 500;
            color: var(--cinza-800);
        }

        .col-titulo span {
            display: block;
            font-size: 0.78rem;
            color: var(--cinza-400);
            margin-top: 2px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.02em;
        }

        .badge::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
        }

        .badge-pendente  { background: var(--amarelo-bg); color: var(--amarelo); }
        .badge-pendente::before { background: #d97706; }

        .badge-concluida { background: var(--verde-bg); color: var(--verde); }
        .badge-concluida::before { background: var(--verde); }

        .col-data {
            font-size: 0.78rem;
            color: var(--cinza-400);
            white-space: nowrap;
        }

        .acoes { display: flex; gap: 6px; justify-content: flex-end; }

        .btn-tabela {
            padding: 6px 14px;
            border-radius: 5px;
            font-size: 0.78rem;
            font-weight: 500;
            text-decoration: none;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.15s;
            font-family: inherit;
        }

        .btn-editar {
            background: var(--azul-light);
            color: var(--azul);
            border-color: #c3d9ff;
        }

        .btn-editar:hover { background: #dbeafe; }

        .btn-excluir {
            background: var(--vermelho-bg);
            color: var(--vermelho);
            border-color: #fca5a5;
        }

        .btn-excluir:hover { background: #fee2e2; }

        /* Botao principal */
        .btn-primary {
            background: var(--azul);
            color: white;
            padding: 8px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.83rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: background 0.15s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-primary:hover { background: var(--azul-hover); }

        /* Vazio */
        .vazio {
            text-align: center;
            padding: 60px 20px;
            color: var(--cinza-400);
        }

        .vazio p { font-size: 0.9rem; margin-top: 8px; }
    </style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <span>TaskManager</span>
        <small>Painel administrativo</small>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-label">Menu</div>
        <a href="index.php" class="nav-item ativo">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
            </svg>
            Tarefas
        </a>
        <a href="pages/criar.php" class="nav-item">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="9"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/>
            </svg>
            Nova Tarefa
        </a>
    </nav>
</aside>

<div class="main">

   
    <div class="topbar">
        <h1>Gerenciamento de Tarefas</h1>
        <div class="topbar-acoes">
            <a href="pages/criar.php" class="btn-primary">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Nova Tarefa
            </a>
        </div>
    </div>

    <div class="conteudo">

        <div class="resumo">
            <div class="card-resumo">
                <div class="label">Total de Tarefas</div>
                <div class="valor azul"><?= $total ?></div>
            </div>
            <div class="card-resumo">
                <div class="label">Pendentes</div>
                <div class="valor amarelo"><?= $pendentes ?></div>
            </div>
            <div class="card-resumo">
                <div class="label">Concluidas</div>
                <div class="valor verde"><?= $concluidas ?></div>
            </div>
        </div>

        <div class="painel">
            <div class="painel-header">
                <div>
                    <h2>Todas as Tarefas <small><?= $total ?> registro(s)</small></h2>
                </div>
            </div>

            <?php if ($mensagem): ?>
                <div class="alerta"><?= htmlspecialchars($mensagem) ?></div>
            <?php endif; ?>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Titulo</th>
                        <th>Status</th>
                        <th>Cadastrado em</th>
                        <th style="text-align:right">Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tarefas)): ?>
                        <tr>
                            <td colspan="5" class="vazio">
                                <svg width="32" height="32" fill="none" stroke="#9ca3af" stroke-width="1.5" viewBox="0 0 24 24">
                                    <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/>
                                    <line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                                </svg>
                                <p>Nenhuma tarefa cadastrada. Clique em "Nova Tarefa" para comecar.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tarefas as $t): ?>
                            <tr>
                                <td class="col-id">#<?= $t['id'] ?></td>
                                <td class="col-titulo">
                                    <strong><?= htmlspecialchars($t['titulo']) ?></strong>
                                    <?php if ($t['descricao']): ?>
                                        <span><?= htmlspecialchars($t['descricao']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $t['status'] ?>">
                                        <?= $t['status'] === 'concluida' ? 'Concluida' : 'Pendente' ?>
                                    </span>
                                </td>
                                <td class="col-data">
                                    <?= date('d/m/Y', strtotime($t['criado_em'])) ?>
                                    <span style="display:block;font-size:0.72rem">
                                        <?= date('H:i', strtotime($t['criado_em'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="acoes">
                                        <a href="pages/editar.php?id=<?= $t['id'] ?>" class="btn-tabela btn-editar">Editar</a>
                                        <a href="pages/excluir.php?id=<?= $t['id'] ?>"
                                           class="btn-tabela btn-excluir"
                                           onclick="return confirm('Deseja excluir esta tarefa?')">Excluir</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

</body>
</html>
