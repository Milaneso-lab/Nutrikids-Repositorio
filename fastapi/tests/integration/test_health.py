def test_health_endpoint(client):
    response = client.get("/health")
    assert response.status_code == 200
    data = response.json()
    assert data["status"] == "ok"
    assert data["api_version"] == "v1"


def test_openapi_available(client):
    response = client.get("/openapi.json")
    assert response.status_code == 200
    schema = response.json()
    assert "/api/v1/ninos" in schema["paths"]
    assert "/api/v1/dashboard/estadisticas" in schema["paths"]
