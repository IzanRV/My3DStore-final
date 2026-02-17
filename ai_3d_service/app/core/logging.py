"""
Configuración de logging
"""

import logging
import sys
from datetime import datetime

def setup_logging():
    """Configurar el sistema de logging"""
    logging.basicConfig(
        level=logging.INFO,
        format='%(asctime)s - %(name)s - %(levelname)s - %(message)s',
        handlers=[
            logging.StreamHandler(sys.stdout),
            logging.FileHandler(f'logs/app_{datetime.now().strftime("%Y%m%d")}.log')
        ]
    )
    
    # Crear directorio de logs si no existe
    import os
    os.makedirs('logs', exist_ok=True)
