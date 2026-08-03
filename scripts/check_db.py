#!/usr/bin/env python3
"""
Script to check PostgreSQL database schema and verify all tables exist
"""
import os
import sys
import psycopg2
from dotenv import load_dotenv

load_dotenv()

def check_database():
    password = os.getenv("DB_PASSWORD") or os.getenv("POSTGRES_PASSWORD")
    if not password:
        print("Define DB_PASSWORD (o POSTGRES_PASSWORD) en el archivo .env antes de ejecutar.")
        sys.exit(1)

    try:
        conn = psycopg2.connect(
            host=os.getenv("DB_HOST", "localhost"),
            port=os.getenv("DB_PORT", "5432"),
            database=os.getenv("DB_DATABASE") or os.getenv("POSTGRES_DB", "nutrikids"),
            user=os.getenv("DB_USERNAME") or os.getenv("POSTGRES_USER", "nutrikids_user"),
            password=password,
        )
        cursor = conn.cursor()

        # Get all tables
        cursor.execute("""
            SELECT table_name
            FROM information_schema.tables
            WHERE table_schema = 'public'
            ORDER BY table_name;
        """)

        tables = cursor.fetchall()
        print("Tablas encontradas en la base de datos:")
        for table in tables:
            print(f"- {table[0]}")

        # Expected tables
        expected_tables = [
            'usuarios', 'contactos', 'comentarios', 'discusiones',
            'pacientes', 'evaluaciones', 'menus', 'reportes',
            'infantes', 'citas', 'alertas', 'alergias',
            'notas_nutriologo', 'menus_semanales',
            'password_reset_tokens', 'cache', 'jobs', 'sessions'
        ]

        found_tables = [t[0] for t in tables]
        missing = [t for t in expected_tables if t not in found_tables]

        if missing:
            print(f"\nTablas faltantes: {missing}")
        else:
            print("\n✅ Todas las tablas esperadas están presentes!")

        # Count records in main tables
        main_tables = ['usuarios', 'pacientes', 'evaluaciones', 'menus', 'reportes']
        print("\nConteo de registros en tablas principales:")
        for table in main_tables:
            if table in found_tables:
                cursor.execute(f"SELECT COUNT(*) FROM {table};")
                count = cursor.fetchone()[0]
                print(f"- {table}: {count} registros")

        cursor.close()
        conn.close()

    except Exception as e:
        print(f"Error conectando a la base de datos: {e}")

if __name__ == "__main__":
    check_database()