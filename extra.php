/* CARD BODY */
        .card-body {
            padding: 40px 20px 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 14px;
        }

        .photo-ring {
            width: 280px;
            height: 280px;
            border-radius: 50%;
            padding: 3px;
            background: linear-gradient(135deg, #0ea5e9, #6366f1);
        }

        .card-photo {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            object-position: top;
            border: 3px solid #0f172a;
            display: block;
        }

        .card-info { text-align: center; width: 100%; }

        .card-name {
            font-size: 25px;
            font-weight: bold;
            color: #f1f5f9;
            line-height: 1.2;
            margin-bottom: 5px;
        }

        .card-position {
            font-size: 15px;
            color: #94a3b8;
            margin-bottom: 16px;
        }

        .status-badge-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .status-label {
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .status-badge {
            font-size: 12px;
            font-weight: bold;
            padding: 5px 18px;
            border-radius: 999px;
            background: #334155;
            color: #94a3b8;
            letter-spacing: 1px;
        }

        .badge-in {
            background: rgba(34,197,94,0.15);
            color: #22c55e;
            border: 1px solid rgba(34,197,94,0.3);
        }

        .badge-out {
            background: rgba(239,68,68,0.15);
            color: #ef4444;
            border: 1px solid rgba(239,68,68,0.3);
        }